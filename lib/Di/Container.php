<?php
/**
 * Annotation based simple DI (Dependency Injection) & AOP (Aspect Oriented Programming).
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2014 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 */
namespace Ranyuen\Di;

use Pimple;

/**
 * Service container.
 */
class Container extends Pimple\Container
{
    /** @var array */
    public static $interceptors = [];
    /** @var Container */
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
                    $facadeClass = $render(file_get_contents('res/FacadeClass.php'));
                    eval('?>'.$facadeClass);
                }
            );
        }
        self::$facade = $c;
    }

    /** @var array */
    private $classes = [];
    /** @var array */
    private $wraps = [];
    /** @var array */
    private $facades = [];

    public function __construct(array $values = [])
    {
        parent::__construct($values);
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
     * @param mixed  $value     The value of the parameter or a closure to
     *                          define an object.
     *
     * @return this
     *
     * @throws \RuntimeException Prevent override of a frozen service.
     */
    public function bind($interface, $key, $value)
    {
        $this[$key] = $value;
        $this->classes[$interface] = $key;

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
        //$render = $render->bindTo(null); // Closure::bindTo isn't impl in HHVM.
        $wrappedClass = $render(file_get_contents('res/WrappedClass.php'));
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
     */
    public function inject($obj)
    {
        if (!is_object($obj)) {
            return $obj;
        }
        $interface = new \ReflectionClass(get_class($obj)); // This must not fail.
        foreach ($interface->getProperties() as $prop) {
            if (!(new Annotation\Inject())->isInjectable($prop)) {
                continue;
            }
            $key = $this->detectKey($prop);
            if (isset($this[$key])) {
                $prop->setAccessible(true);
                $prop->setValue($obj, $this[$key]);
            }
        }

        return $obj;
    }

    /**
     * Create a new instance and injection.
     *
     * @param string $class Create an instance.
     * @param array  $args  Arguments which doesn't inject.
     *
     * @return object
     *
     * @throws \ReflectionException The class doesn't exist.
     */
    public function newInstance($class, $args = [])
    {
        if (isset($this->wraps[$class])) {
            $class = $this->wraps[$class];
        } else {
            $this->wrapClass($class);
            $class = $this->wraps[$class];
        }
        $class = new \ReflectionClass($class);
        $method = $class->hasMethod('__construct') ?
            $class->getMethod('__construct') :
            null;
        if ($method) {
            foreach ($method->getParameters() as $i => $param) {
                if (isset($args[$key = $param->getName()])) {
                    array_splice($args, $i, 0, [$args[$key]]);
                } elseif (isset($this[$key = $this->detectKey($param)])) {
                    array_splice($args, $i, 0, [$this[$key]]);
                }
            }
        }
        $obj = $class->newInstanceArgs($args);
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

    /**
     * Detect what key to get the value.
     *
     * Priority.
     * 1. Inject annotation with name.
     * 2. Named annotation.
     * 3. Type hinting and type of var annotation.
     * 4. Variable name.
     *
     * @param \ReflectionParameter|\ReflectionProperty $obj Target.
     *
     * @return string
     */
    private function detectKey($obj)
    {
        $key = $obj->getName();
        if ($obj instanceof \ReflectionProperty) {
            if ($injectName = (new Annotation\Inject())->getInject($obj)) {
                $named = [$key => $injectName];
            } else {
                $named = (new Annotation\Named())->getNamed($obj);
            }
        } else {
            $named = (new Annotation\Named())->getNamed($obj->getDeclaringFunction());
        }
        if (isset($named[$key])) {
            $key = $named[$key];
        } elseif (isset($this->classes[$type = (new Reflection\Type())->getType($obj)])) {
            $key = $this->classes[$type];
        }

        return $key;
    }

    /**
     * @param string $class Will be wrapped.
     *
     * @return void
     *
     * @throws \ReflectionException The class doesn't exist.
     */
    private function wrapClass($class)
    {
        $class = new \ReflectionClass($class);
        $wraps = (new Annotation\Wrap())->gatherWraps($class);
        foreach ($wraps as $advice => $methods) {
            $this->wrap($class->getName(), $methods, $this[$advice]);
        }
        if (!isset($this->wraps[$class->getName()])) {
            $this->wraps[$class->getName()] = $class->getName();
        }
    }
}
