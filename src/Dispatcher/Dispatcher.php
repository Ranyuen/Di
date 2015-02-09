<?php
/**
 * Annotation based simple DI & AOP at PHP.
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2015 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 */

namespace Ranyuen\Di\Dispatcher;

use Ranyuen\Di\Container;

/**
 * Inject to callables' args.
 */
class Dispatcher
{
    /**
     * Detect the string is a preg expression or not.
     *
     * @param string $str Some string.
     *
     * @return bool
     */
    public static function isRegex($str)
    {
        if (!(is_string($str) && preg_match('/\A[^A-Za-z0-9\\\s]/', $str))) {
            return false;
        }
        $delimiter = $str[0];
        $delimiters = [
            '(' => ')',
            '{' => '}',
            '[' => ']',
            '<' => '>',
        ];
        if (isset($delimiters[$delimiter])) {
            $delimiter = $delimiters[$delimiter];
        }

        return !!preg_match('/'.preg_quote($delimiter, '/').'[imsxeADSUXJu]*\z/', $str);
    }

    private $c;
    private $named  = [];
    private $nameds = [];
    private $typed  = [];

    public function __construct(Container $c = null)
    {
        if ($c) {
            $this->c = $c;
        }
    }

    /**
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
     * @param string $class Class name.
     * @param mixed  $val   Value.
     *
     * @return void
     */
    public function setTypedArg($class, $val)
    {
        if (!class_exists($class) && !interface_exists($class)) {
            return;
        }
        $this->typed[$class] = $val;
        // foreach(array_merge(
        //     class_implements($class),
        //     class_parents($class),
        //     class_uses($class)
        // ) as $parent) {
        //     $this->typed[$parent] = $val;
        // }
    }

    /**
     * @param string|callable $func    Invokable.
     * @param array           $args    Arguments.
     * @param ojbect          $thisObj This of the method.
     *
     * @return mixed
     */
    public function invoke($func, array $args = [], $thisObj = null)
    {
        list($func, $params) = $this->toCallable($func, $thisObj);
        foreach ($params as $i => $param) {
            if (is_null($var = $this->getByParam($param))) {
                continue;
            }
            array_splice($args, $i, 0, [$var]);
        }

        return call_user_func_array($func, $args);
    }

    /**
     * Get a value from the containers by the ReflectionParameter.
     *
     * @param \ReflectionParameter $param Key.
     *
     * @return mixed|null
     */
    public function getByParam(\ReflectionParameter $param)
    {
        if ($type = $param->getClass()) {
            $type = $type->name;
            if (isset($this->typed[$type])) {
                return $this->typed[$type];
            }
            if (!is_null($var = $this->c->getByType($type))) {
                return $var;
            }
        }
        $name = $param->name;
        if (isset($this->named[$name])) {
            return $this->named[$name];
        }
        foreach ($this->nameds as $named) {
            if (isset($named[$name])) {
                return $named[$name];
            }
        }
        if (isset($this->c[$name])) {
            return $this->c[$name];
        }
    }

    private function toCallable($func, $thisObj = null)
    {
        if (is_callable($func)) {
            return $this->callableToCollable($func);
        }
        if (self::isRegex($func)) {
            return $this->regexToCallable($func);
        }
        if (is_string($func) && (false !== strpos($func, '@'))) {
            return $this->methodToCallable($func, $thisObj);
        }
        throw new Exception('Not a callable: '.(string) $func);
    }

    private function callableToCollable($func)
    {
        if ($func instanceof \Closure
            || (is_string($func) && function_exists($func))
        ) {
            $params = (new \ReflectionFunction($func))->getParameters();
        } elseif (is_array($func)) {
            list($class, $method) = $func;
            if (is_object($class)) {
                $class = get_class($class);
            }
            $params = (new \ReflectionMethod($class, $method))->getParameters();
        } else {
            $params = (new \ReflectionMethod($func))->getParameters();
        }

        return [$func, $params];
    }

    private function regexToCallable($pattern)
    {
        $invocation = function ($subject, array &$matches = null, $flags = 0, $offset = 0) use ($pattern) {
            return preg_match($pattern, $subject, $matches, $flags, $offset);
        };
        $params = (new \ReflectionFunction($invocation))->getParameters();

        return [$invocation, $params];
    }

    private function methodToCallable($func, $thisObj = null)
    {
        list($class, $method) = explode('@', $func);
        $invocation = function () use ($thisObj, $class, $method) {
            if (!is_object($thisObj) || !($thisObj instanceof $class)) {
                if (isset($this->c[$class])) {
                    $thisObj = $this->c[$class];
                } elseif ($thisObj = $this->c->getByType($class)) {
                    null;
                } else {
                    $thisObj = $this->c->newInstance($class);
                }
            }

            return call_user_func_array([$thisObj, $method], func_get_args());
        };
        $params = (new \ReflectionMethod($class, $method))->getParameters();

        return [$invocation, $params];
    }
}
