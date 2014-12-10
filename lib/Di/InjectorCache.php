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

/**
 * Cache dependency graphs.
 */
class InjectorCache
{
    private static $cache = [];

    /**
     * Get inject injetor.
     *
     * @param Container $c             Container.
     * @param string    $interfaceName Class name.
     *
     * @return callable
     *
     * @throws \RuntimeException The class doesn't exist.
     */
    public static function getInject(Container $c, $interfaceName)
    {
        $cache = self::initCache($c)['inject'];
        if (!isset($cache[$interfaceName])) {
            $interface = new \ReflectionClass($interfaceName);
            $annotation = new Annotation\Inject();
            $deps = [];
            foreach ($interface->getProperties() as $prop) {
                if (!$annotation->isInjectable($prop)) {
                    continue;
                }
                $key = $c->detectKey($prop);
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
            $cache[$interfaceName] = $injector;
        }

        return $cache[$interfaceName];
    }

    private static function initCache(Container $c)
    {
        $id = spl_object_hash($c);
        if (!isset(self::$cache[$id])) {
            self::$cache[$id] = [
                'inject'      => [],
                'newInstance' => [],
            ];
        }

        return self::$cache[$id];
    }
}
