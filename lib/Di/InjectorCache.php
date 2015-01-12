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
    private static $cache;

    /**
     * Remove cache reference of the container.
     *
     * @param Container $c Container.
     *
     * @return void
     */
    public static function removeCache(Container $c)
    {
        if (isset(self::$cache[$c])) {
            unset(self::$cache[$c]);
        }
    }

    /**
     * Get inject injetor.
     *
     * @param Container $c         Container.
     * @param string    $className Class name.
     *
     * @return callable
     *
     * @throws \ReflectionException The class doesn't exist.
     */
    public static function getInject(Container $c, $className)
    {
        $cache = self::initCache($c);
        if (!isset($cache['inject'][$className])) {
            $class = new \ReflectionClass($className);
            $annotation = new Annotation\Inject();
            $deps = [];
            foreach ($class->getProperties() as $prop) {
                if (!$annotation->isInjectable($prop)) {
                    continue;
                }
                $key = self::detectKey($c, $prop);
                if (isset($c[$key])) {
                    $prop->setAccessible(true);
                    $deps[] = [$prop, $key];
                }
            }
            $injector = function ($obj) use ($c, $deps) {
                foreach ($deps as $dep) {
                    list($prop, $key) = $dep;
                    $prop->setValue($obj, $c[$key]);
                }
            };
            $cache['inject'][$className] = $injector;
            self::$cache[$c] = $cache;
        }

        return $cache['inject'][$className];
    }

    /**
     * @param Container $c         Container.
     * @param string    $className Class name.
     *
     * @return callable
     *
     * @throws \ReflectionException The class doesn't exist.
     */
    public static function getNewInstance(Container $c, $className)
    {
        $cache = self::initCache($c);
        if (!isset($cache['newInstance'][$className])) {
            $class = new \ReflectionClass($className);
            if ($class->hasMethod('__construct')) {
                $deps = [];
                $method = $class->getMethod('__construct');
                $params = $method->getParameters();
                foreach ($params as $param) {
                    $key = self::detectKey($c, $param);
                    if (isset($c[$key])) {
                        $deps[$param->getName()] = $key;
                    }
                }
                $injector = function ($args) use ($c, $class, $params, $deps) {
                    foreach ($params as $i => $param) {
                        if (isset($args[$key = $param->getName()])) {
                            array_splice($args, $i, 0, [$args[$key]]);
                        } elseif (isset($deps[$param->getName()])) {
                            $key = $deps[$param->getName()];
                            array_splice($args, $i, 0, [$c[$key]]);
                        }
                    }

                    return $class->newInstanceArgs($args);
                };
            } else {
                $injector = function () use ($class) {
                    return $class->newInstanceArgs();
                };
            }
            $cache['newInstance'][$className] = $injector;
            self::$cache[$c] = $cache;
        }

        return $cache['newInstance'][$className];
    }

    private static function initCache(Container $c)
    {
        if (is_null(self::$cache)) {
            self::$cache = new \SplObjectStorage();
        }
        if (!isset(self::$cache[$c])) {
            self::$cache[$c] = [
                'inject'      => [],
                'newInstance' => [],
            ];
        }

        return self::$cache[$c];
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
     * @param Container                                $c   Container.
     * @param \ReflectionParameter|\ReflectionProperty $obj Target.
     *
     * @return string
     */
    private static function detectKey(Container $c, $obj)
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
            && isset($c->classes[$type])
        ) {
            $key = $c->classes[$type];
        }

        return $key;
    }
}
