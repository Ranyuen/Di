<?php
/**
 * Annotation based, simple DI (Dependency Injector) extending Pimple.
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2014 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 */
namespace Ranyuen\Di;

use Pimple;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * Service container.
 */
class Container extends Pimple\Container
{
    /** @var array */
    private $classNames = [];

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
     * @throws RuntimeException Prevent override of a frozen service.
     */
    public function bind($interface, $key, $value)
    {
        $this[$key] = $value;
        $this->classNames[$interface] = $key;
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
        try {
            $interface = new ReflectionClass(get_class($obj));
        } catch (ReflectionException $ex) {
            return $obj;
        }
        foreach ($interface->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            if (!(new Annotation())->isInjectable($prop)) {
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
     * @param string $interface Create an instance.
     * @param array  $args      Arguments which doesn't inject.
     *
     * @return object
     *
     * @throws ReflectionException The class doesn't exist.
     */
    public function newInstance($interface, $args = [])
    {
        $interface = new ReflectionClass($interface);
        $method = $interface->hasMethod('__construct') ?
            $interface->getMethod('__construct') :
            null;
        if ($method) {
            $named = (new Annotation())->getNamed($method);
            $idx = 0;
            foreach ($method->getParameters() as $param) {
                $key = $this->detectKey($param, $named);
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

    /**
     * @param ReflectionParameter|ReflectionProperty $obj   Target.
     * @param array                                  $named Named of __construct.
     *
     * @return string
     */
    private function detectKey($obj, $named = [])
    {
        $key = $obj->getName();
        if ($obj instanceof ReflectionProperty) {
            $named = (new Annotation())->getNamed($obj);
        }
        if (isset($named[$key])) {
            $key = $named[$key];
        } else {
            $type = (new Annotation())->getType($obj);
            if (isset($this->classNames[$type])) {
                $key = $this->classNames[$type];
            }
        }

        return $key;
    }
}
