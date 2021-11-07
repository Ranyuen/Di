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

declare(strict_types=1);

namespace Ranyuen\Di;

use Ranyuen\Di\Dispatcher\NakedDispatcher;
use Ranyuen\Di\Dispatcher\ParametrizedInvokable;
use Ranyuen\Di\Reflection\KeyReflector;

/**
 * Cache dependency graphs.
 */
class InjectorCache
{
    /**
     * DI container.
     *
     * @var Container
     */
    private $c;
    /**
     * Cache.
     *
     * @var array
     */
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
            $reflector = new KeyReflector($this->c);
            foreach ($class->getProperties() as $prop) {
                if (!$annotation->isInjectable($prop)) {
                    continue;
                }
                $key = $reflector->detectKey($prop);
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
                $dispatcher = new NakedDispatcher($this->c);
                $func = new ParametrizedInvokable(
                    function () use ($class) {
                        $func = [$class, 'newInstanceArgs'];
                        return $func(func_get_args());
                    },
                    $class->getMethod('__construct')->getParameters()
                );
                $injector = function ($args) use ($dispatcher, $func) {
                    return $dispatcher->invoke($func, $args);
                };

            // $deps = [];
                // $method = $class->getMethod('__construct');
                // $params = $method->getParameters();
                // foreach ($params as $param) {
                //     $key = self::detectKey($param);
                //     if (isset($this->c[$key])) {
                //         $deps[$param->name] = $key;
                //     }
                // }
                // $injector = function ($args) use ($class, $params, $deps) {
                //     foreach ($params as $i => $param) {
                //         if (isset($args[$key = $param->name])) {
                //             array_splice($args, $i, 0, [$args[$key]]);
                //         } elseif (isset($deps[$param->name])) {
                //             $key = $deps[$param->name];
                //             array_splice($args, $i, 0, [$this->c[$key]]);
                //         }
                //     }

                //     return $class->newInstanceArgs($args);
                // };
            } else {
                $injector = function () use ($class) {
                    return $class->newInstanceArgs();
                };
            }
            $this->cache[$className]['newInstance'] = $injector;
        }

        return $this->cache[$className]['newInstance'];
    }
}
