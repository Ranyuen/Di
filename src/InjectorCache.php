<?php
/**
 * Annotation based simple DI & AOP at PHP.
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2015 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 */

namespace Ranyuen\Di;

/**
 * Cache dependency graphs.
 */
class InjectorCache
{
    /** @var Container */
    private $c;
    /** @var array */
    private $cache = [];

    public function __construct(Container $c)
    {
        $this->c = $c;
    }

    /**
     * Get inject injetor.
     *
     * @param string $className Class name.
     *
     * @return callable
     *
     * @throws \ReflectionException The class doesn't exist.
     */
    public function getInject($className)
    {
        if (!isset($this->cache[$className])) {
            $this->cache[$className] = [
                'inject'      => null,
                'newInstance' => null,
            ];
        }
        if (!$this->cache[$className]['inject']) {
            $class = new \ReflectionClass($className);
            $annotation = new Annotation\Inject();
            $deps = [];
            foreach ($class->getProperties() as $prop) {
                if (!$annotation->isInjectable($prop)) {
                    continue;
                }
                $key = self::detectKey($prop);
                if (isset($this->c[$key])) {
                    $prop->setAccessible(true);
                    $deps[] = [$prop, $key];
                }
            }
            $injector = function ($obj) use ($deps) {
                foreach ($deps as $dep) {
                    list($prop, $key) = $dep;
                    $prop->setValue($obj, $this->c[$key]);
                }
            };
            $this->cache[$className]['inject'] = $injector;
        }

        return $this->cache[$className]['inject'];
    }

    /**
     * Get newInstance injector.
     *
     * @param string $className Class name.
     *
     * @return callable
     *
     * @throws \ReflectionException The class doesn't exist.
     */
    public function getNewInstance($className)
    {
        if (!isset($this->cache[$className])) {
            $this->cache[$className] = [
                'inject'      => null,
                'newInstance' => null,
            ];
        }
        if (!$this->cache[$className]['newInstance']) {
            $class = new \ReflectionClass($className);
            if ($class->hasMethod('__construct')) {
                $deps = [];
                $method = $class->getMethod('__construct');
                $params = $method->getParameters();
                foreach ($params as $param) {
                    $key = self::detectKey($param);
                    if (isset($this->c[$key])) {
                        $deps[$param->getName()] = $key;
                    }
                }
                $injector = function ($args) use ($class, $params, $deps) {
                    foreach ($params as $i => $param) {
                        if (isset($args[$key = $param->getName()])) {
                            array_splice($args, $i, 0, [$args[$key]]);
                        } elseif (isset($deps[$param->getName()])) {
                            $key = $deps[$param->getName()];
                            array_splice($args, $i, 0, [$this->c[$key]]);
                        }
                    }

                    return $class->newInstanceArgs($args);
                };
            } else {
                $injector = function () use ($class) {
                    return $class->newInstanceArgs();
                };
            }
            $this->cache[$className]['newInstance'] = $injector;
        }

        return $this->cache[$className]['newInstance'];
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
            $named = (new Annotation\Named())
                ->getNamed($obj->getDeclaringFunction());
        }
        if (isset($named[$key])) {
            $key = $named[$key];
        } elseif (($type = (new Reflection\Type())->getType($obj))
            && isset($this->c->classes[$type])
        ) {
            $key = $this->c->classes[$type];
        }

        return $key;
    }
}
