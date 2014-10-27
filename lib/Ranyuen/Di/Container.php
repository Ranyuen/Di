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
 * @link      https://github.com/Ranyuen/Di
 */
namespace Ranyuen\Di;

use Pimple;
use ReflectionClass;
use ReflectionException;

/**
 * Simple DI (Dependency Injector) extending Pimple.
 *
 * @category Ranyuen
 * @package  Ranyuen
 * @author   Ranyuen <cal_pone@ranyuen.com>
 * @author   ne_Sachirou <utakata.c4se@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL
 * @link     https://github.com/Ranyuen/Di
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
            if (!(new Annotation())->isInjectable($prop)) {
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
                $named = (new Annotation())->getNamed($prop);
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
        if ($method && (new Annotation())->isInjectable($method)) {
            $named = (new Annotation())->getNamed($method);
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
}
