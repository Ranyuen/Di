<?php
/**
 * Annotation based simple DI & AOP at PHP.
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2021 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 * @link      https://github.com/Ranyuen/Di
 */

namespace Ranyuen\Di;

use Pimple;

/**
 * Service container.
 */
class Container extends Pimple\Container
{
    /**
     * Method interceptors.
     *
     * @var array
     */
    public static $interceptors = [];
    /**
     * Singleton facades.
     *
     * @var Container
     */
    public static $facade;

    /**
     * Set the container as facade.
     *
     * @param Container $c The container for facade.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.EvalExpression)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function setAsFacade(Container $c)
    {
        if (!self::$facade) {
            spl_autoload_register(
                function ($interface) {
                    $c = Container::$facade;
                    if (!$c->getFacadeContent($interface)) {
                        return;
                    }
                    $render = function () use ($interface) {
                        ob_start();
                        eval('?>'.func_get_arg(0));

                        return ob_get_clean();
                    };
                    $facadeClass = $render(file_get_contents(__dir__.'/../res/FacadeClass.php'));
                    eval('?>'.$facadeClass);
                }
            );
        }
        self::$facade = $c;
    }

    /**
     * Typed bindings.
     *
     * @var array
     */
    public $classes = [];

    /**
     * AOP wrappers.
     *
     * @var array
     */
    private $wraps = [];
    /**
     * Singleton facades.
     *
     * @var array
     */
    private $facades = [];
    /**
     * Cache.
     *
     * @var InjectorCache
     */
    private $cache;

    public function __construct(array $vals = [])
    {
        parent::__construct($vals);
        $this->cache = new InjectorCache($this);
        if (!self::$facade) {
            self::setAsFacade($this);
        }
    }

    /**
     * Bind a value with the class name.
     *
     * @param string $interface The class name of the value.
     * @param string $key       The unique identifier for the parameter or
     *                          object.
     * @param mixed  $val       The value of the parameter or a closure to
     *                          define an object.
     *
     * @return this
     *
     * @throws \RuntimeException Prevent override of a frozen service.
     */
    public function bind($interface, $key, $val)
    {
        $this[$key] = $val;
        $this->classes[$interface] = $key;
        foreach (array_merge(
            class_implements($interface),
            class_parents($interface),
            class_uses($interface)
        ) as $parent) {
            if (!isset($this->classes[$parent])) {
                $this->classes[$parent] = $val;
            }
        }

        return $this;
    }

    /**
     * Get object by type.
     *
     * @param string $interface FQN. This must equal to the FQN you use in bind().
     *
     * @return mixed
     */
    public function getByType($interface)
    {
        if (!isset($this->classes[$interface])) {
            return;
        }

        return $this[$this->classes[$interface]];
    }

    /**
     * AOP.
     *
     * @param string   $interface   Class name.
     * @param mixed[]  $matchers    Pointcut.
     * @param callable $interceptor Advice. function(callable $invocation, array $args)
     *
     * @return this
     *
     * @throws \ReflectionException The class doesn't exist.
     *
     * @SuppressWarnings(PHPMD.EvalExpression)
     * @SuppressWarnings(PHPMD.StaticAccess)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function wrap($interface, $matchers, callable $interceptor)
    {
        $uniqid = uniqid();
        self::$interceptors[$uniqid] = $interceptor;
        if (isset($this->wraps[$interface])) {
            $parent = $this->wraps[$interface];
            $this->wraps[$interface] = "Tmp$uniqid";
            $interface = $parent;
        } else {
            $this->wraps[$interface] = "Tmp$uniqid";
        }
        $interface = new \ReflectionClass($interface);
        $render = function () use ($interface, $matchers, $uniqid) {
            ob_start();
            eval('?>'.func_get_arg(0));

            return ob_get_clean();
        };
        $render = $render->bindTo(null);
        $wrappedClass = $render(file_get_contents(__dir__.'/../res/WrappedClass.php'));
        $dir = sys_get_temp_dir();
        $file = fopen("$dir/$uniqid", 'w');
        fwrite($file, $wrappedClass);
        include_once "$dir/$uniqid";
        fclose($file);

        return $this;
    }

    /**
     * Inject properties.
     *
     * @param object $obj Target object.
     *
     * @return object
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function inject($obj)
    {
        if (!is_object($obj)) {
            return $obj;
        }
        $injector = $this->cache->getInject(get_class($obj)); // This must not fail.
        $injector($obj);

        return $obj;
    }

    /**
     * Create a new instance and injection.
     *
     * @param string $interface Create an instance.
     * @param array  $args      Arguments which doesn't inject.
     *
     * @return object
     *
     * @throws \ReflectionException The class doesn't exist.
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function newInstance($interface, $args = [])
    {
        if (isset($this->wraps[$interface])) {
            $interface = $this->wraps[$interface];
        } else {
            $this->wrapClass($interface);
            $interface = $this->wraps[$interface];
        }
        $injector = $this->cache->getNewInstance($interface);
        $obj = $injector($args);
        $this->inject($obj);

        return $obj;
    }

    /**
     * Register facade.
     *
     * @param string $facadeName  Facade name.
     * @param string $contentName Content name.
     *
     * @return this
     */
    public function facade($facadeName, $contentName)
    {
        $this->facades[$facadeName] = $contentName;

        return $this;
    }

    /**
     * Get facade content.
     *
     * @param string $facadeName Facade name.
     *
     * @return mixed
     */
    public function getFacadeContent($facadeName)
    {
        if (!isset($this->facades[$facadeName])) {
            return;
        }
        $contentName = $this->facades[$facadeName];
        if (!isset($this[$contentName])) {
            return;
        }

        return $this[$contentName];
    }

    private function wrapClass($interface)
    {
        $interface = new \ReflectionClass($interface);
        $wraps = (new Annotation\Wrap())->gatherWraps($interface);
        foreach ($wraps as $advice => $methods) {
            $this->wrap($interface->getName(), $methods, $this[$advice]);
        }
        if (!isset($this->wraps[$interface->getName()])) {
            $this->wraps[$interface->getName()] = $interface->getName();
        }
    }
}
