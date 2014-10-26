<?php
/**
 * Simple DI (Dependency Injector) extending Pimple.
 *
 * PHP version 5
 *
 * @category  Ranyuen
 * @package   Ranyuen
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2014 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 * @version   GIT: 0.0.1
 * @link      https://github.com/Ranyuen/web
 */
namespace Ranyuen;

use \Pimple;
use \ReflectionClass;
use \ReflectionException;

/**
 * Simple DI (Dependency Injector) extending Pimple.
 *
 * cf. fabpot/Pimple
 * cf. koriym/Ray.Di
 *
 * @category Ranyuen
 * @package  Ranyuen
 * @author   Ranyuen <cal_pone@ranyuen.com>
 * @author   ne_Sachirou <utakata.c4se@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL
 * @link     https://github.com/Ranyuen/web
 *
 * @example
 * <code>
 * class Momonga { }
 *
 * $container = new \Ranyuen\Container;
 * $container->bind('Momonga', 'momonga', function ($c) { return new Momonga; });
 *
 * class Yuraru
 * {
 *     /** @Inject * /
 *     public function __construct(Momonga $momonga) { }
 * }
 *
 * $yuraru = $container->newInstance('Yuraru');
 *
 * class Gardea
 * {
 *     /**
 *      * @Inject
 *      * @var Momonga
 *      * /
 *     public $momonga;
 * }
 *
 * $gardea = $container->newInstance();
 *
 * $gardea = new Gardea;
 * $container->inject($gardea);
 * </code>
 */
class Container extends Pimple\Container
{
    /** @var array */
    private $_class_names = [];

    /**
     * Bind a value with the class name.
     *
     * @param string $interface The class name of the value.
     * @param string $id        The unique identifier for the parameter or object.
     * @param mixed  $value     The value of the parameter or a closure to
     *                          define an object.
     *
     * @return void
     *
     * @throws \RuntimeException Prevent override of a frozen service.
     */
    public function bind($interface, $id, $value)
    {
        $this->_class_names[$interface] = $id;
        $this[$id] = $value;
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
        $interface = new ReflectionClass(get_class($obj));
        foreach ($interface->getProperties() as $prop) {
            if (!$this->hasAnnotationInject($prop)) {
                continue;
            }
            $matches = [];
            if (preg_match(
                '/^\\s*(?:\\/\\*)?\\*\\s*@var\s+([a-zA-Z0-9_\\x7f-\\xff\\\\]+)/m',
                $prop->getDocComment(),
                $matches
            )) {
                $prop_class = $matches[1];
            }
            if (isset($this->_class_names[$prop_class])) {
                $id = $this->_class_names[$prop_class];
            } else {
                $id = $prop->getName();
                $named = $this->getNamed($prop);
                if (isset($named[$id])) {
                    $id = $named[$id];
                }
            }
            if (isset($this[$id])) {
                $prop->setAccessible(true);
                $prop->setValue($obj, $this[$id]);
            }
        }

        return $obj;
    }

    /**
     * Create a new instance and injection.
     *
     * @param string $interface Create an instance.
     * @param array  $args      Arguments which doesn't inject.
     *
     * @return object
     */
    public function newInstance($interface, $args = [])
    {
        $interface = new ReflectionClass($interface);
        try {
            $method = $interface->getMethod('__construct');
        } catch (ReflectionException $ex) {
            $method = null;
        }
        if ($method && $this->hasAnnotationInject($method)) {
            $named = $this->getNamed($method);
            $i = 0;
            foreach ($method->getParameters() as $param) {
                $param_class = $param->getClass();
                if ($param_class) {
                    $param_class = $param_class->getName();
                }
                if (isset($this->_class_names[$param_class])) {
                    $id = $this->_class_names[$param_class];
                } else {
                    $id = $param->getName();
                    if (isset($named[$id])) {
                        $id = $named[$id];
                    }
                }
                if (isset($this[$id])) {
                    array_splice($args, $i, 0, [$this[$id]]);
                }
                ++$i;
            }
        }
        $obj = $interface->newInstanceArgs($args);
        $this->inject($obj);

        return $obj;
    }

    /**
     * @return boolean
     */
    private function hasAnnotationInject($target)
    {
        return !!preg_match(
            '/^\\s*(?:\\/\\*)?\\*\\s*@Inject/m',
            $target->getDocComment()
        );
    }

    /**
     * @return array
     */
    private function getNamed($target)
    {
        $matches = [];
        $named = [];
        if (preg_match(
            '/^\\s*(?:\\/\\*)?\\*\\s*@Named\\([\'"]([^\'"]+)[\'"]\\)/m',
            $target->getDocComment(),
            $matches
        )) {
            foreach (explode(',', $matches[1]) as $field) {
                $field = explode('=', $field);
                $named[$field[0]] = $field[1];
            }
        }

        return $named;
    }
}
