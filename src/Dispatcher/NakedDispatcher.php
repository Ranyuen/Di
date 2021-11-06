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

namespace Ranyuen\Di\Dispatcher;

use Ranyuen\Di\Container;
use Ranyuen\Di\Reflection\KeyReflector;

/**
 * Dispatcher service.
 */
class NakedDispatcher
{
    private $c;
    private $named  = [];
    private $nameds = [];
    private $typed  = [];

    public function __construct(Container $c = null)
    {
        $this->c = $c;
    }

    /**
     * Set an argument with the name.
     *
     * @param string $name Key.
     * @param mixed  $val  Value.
     *
     * @return void
     */
    public function setNamedArg($name, $val)
    {
        $this->named[$name] = $val;
    }

    /**
     * Set arguments by the assoc.
     *
     * @param array|ArrayAccess $array Key-values.
     *
     * @return void
     */
    public function setNamedArgs($array)
    {
        if (is_array($array)) {
            $this->named = array_merge($this->named, $array);

            return;
        }
        if (!($array instanceof \ArrayAccess)) {
            throw new Exception('the arg must implement ArrayAccess.');
        }
        $this->nameds[] = $array;
    }

    /**
     * Set an argument with the class name.
     *
     * @param string $interface Class name.
     * @param mixed  $val       Value.
     *
     * @return void
     */
    public function setTypedArg($interface, $val)
    {
        if (!class_exists($interface) && !interface_exists($interface)) {
            return;
        }
        $this->typed[$interface] = $val;
        foreach (array_merge(
            class_implements($interface),
            class_parents($interface),
            class_uses($interface)
        ) as $parent) {
            if (!isset($this->typed[$parent])) {
                $this->typed[$parent] = $val;
            }
        }
    }

    /**
     * Dispatch the function.
     *
     * @param callable $func Invokable.
     * @param array    $args Arguments.
     *
     * @return mixed
     */
    public function invoke(callable $func, array $args = [])
    {
        $params = $this->getParameters($func);
        foreach ($params as $i => $param) {
            if (isset($args[$param->name])) {
                array_splice($args, $i, 0, [$args[$param->name]]);
            } elseif ($this->hasParametrizedValue($param, $var)) {
                array_splice($args, $i, 0, [$var]);
            }
        }

        return call_user_func_array($func, $args);
    }

    /**
     * Get a value from the containers by the ReflectionParameter.
     *
     * @param \ReflectionParameter $param  Key.
     * @param mixed                $result Value.
     *
     * @return bool
     */
    private function hasParametrizedValue(\ReflectionParameter $param, &$result)
    {
        $result = null;
        if ($type = $param->getClass()) {
            $type = $type->name;
            if (isset($this->typed[$type])) {
                $result = $this->typed[$type];
                return true;
            }
        }
        $name = $param->name;
        if (isset($this->named[$name])) {
            $result = $this->named[$name];
            return true;
        }
        foreach ($this->nameds as $named) {
            if (isset($named[$name])) {
                $result = $named[$name];
                return true;
            }
        }
        if (isset($this->c[$key = (new KeyReflector($this->c))->detectKey($param)])) {
            $result = $this->c[$key];
            return true;
        }
        return false;
    }

    public function getParameters(callable $func)
    {
        if ($func instanceof ParametrizedInvokable) {
            return $func->getParameters();
        }
        if ($func instanceof \Closure || (is_string($func) && function_exists($func))) {
            return (new \ReflectionFunction($func))->getParameters();
        }
        if (is_array($func)) {
            list($interface, $method) = $func;
            if (is_object($interface)) {
                $interface = get_class($interface);
            }
            return (new \ReflectionMethod($interface, $method))->getParameters();
        }
        return (new \ReflectionMethod($func))->getParameters();
    }
}
