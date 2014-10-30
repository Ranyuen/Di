<?php
/**
 * Simple Ray.Di style DI (Dependency Injector) extending Pimple.
 *
 * PHP version 5
 *
 * @category  Di
 * @package   Ranyuen\Di
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2014 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 * @version   GIT: 0.0.2
 * @link      https://github.com/Ranyuen/Di
 */
namespace Ranyuen\Di;

use Pimple;
use ReflectionClass;
use ReflectionException;

/**
 * Simple Ray.Di style DI (Dependency Injector) extending Pimple.
 *
 * @category Di
 * @package  Ranyuen\Di
 * @author   Ranyuen <cal_pone@ranyuen.com>
 * @author   ne_Sachirou <utakata.c4se@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL
 * @link     https://github.com/Ranyuen/Di
 */
class Container extends Pimple\Container
{
    /** @var array */
    private $_classNames = [];

    /**
     * Bind a value with the class name.
     *
     * @param string $interface The class name of the value.
     * @param string $key       The unique identifier for the parameter or
     *                          object.
     * @param mixed  $value     The value of the parameter or a closure to
     *                          define an object.
     *
     * @return void
     *
     * @throws \RuntimeException Prevent override of a frozen service.
     */
    public function bind($interface, $key, $value)
    {
        $this->_classNames[$interface] = $key;
        $this[$key] = $value;
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
            if (!(new Annotation())->isInjectable($prop)) {
                continue;
            }
            $matches = [];
            $propClass = null;
            if (preg_match(
                '/^\\s*(?:\\/\\*)?\\*\\s*@var\s+([a-zA-Z0-9_\\x7f-\\xff\\\\]+)/m',
                $prop->getDocComment(),
                $matches
            )) {
                $propClass = $matches[1];
            }
            if ($propClass && isset($this->_classNames[$propClass])) {
                $key = $this->_classNames[$propClass];
            } else {
                $key = $prop->getName();
                $named = (new Annotation())->getNamed($prop);
                if (isset($named[$key])) {
                    $key = $named[$key];
                }
            }
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
        if ($method && (new Annotation())->isInjectable($method)) {
            $named = (new Annotation())->getNamed($method);
            $idx = 0;
            foreach ($method->getParameters() as $param) {
                $paramClass = $param->getClass();
                if ($paramClass) {
                    $paramClass = $paramClass->getName();
                }
                if (isset($this->_classNames[$paramClass])) {
                    $key = $this->_classNames[$paramClass];
                } else {
                    $key = $param->getName();
                    if (isset($named[$key])) {
                        $key = $named[$key];
                    }
                }
                if (isset($this[$key])) {
                    array_splice($args, $idx, 0, [$this[$key]]);
                }
                ++$idx;
            }
        }
        $obj = $interface->newInstanceArgs($args);
        $this->inject($obj);

        return $obj;
    }
}
