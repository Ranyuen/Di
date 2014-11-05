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

/**
 * Service container.
 */
class Container extends Pimple\Container
{
    /** @var array */
    private $classes = [];

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
        $this[$key] = $value;
        $this->classes[$interface] = $key;
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
     * @param string $class Create an instance.
     * @param array  $args  Arguments which doesn't inject.
     *
     * @return object
     *
     * @throws \ReflectionException The class doesn't exist.
     */
    public function newInstance($class, $args = [])
    {
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
     * Detect what key to get the value.
     *
     * Priority.
     * 1. Inject with name annotation.
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
        $annotation = new Annotation();
        $key = $obj->getName();
        if ($obj instanceof \ReflectionProperty) {
            if ($injectName = $annotation->getInject($obj)) {
                $named = [$key => $injectName];
            } else {
                $named = $annotation->getNamed($obj);
            }
        } else {
            $named = $annotation->getNamed($obj->getDeclaringFunction());
        }
        if (isset($named[$key])) {
            $key = $named[$key];
        } elseif (isset($this->classes[$type = $annotation->getType($obj)])) {
            $key = $this->classes[$type];
        }

        return $key;
    }
}
