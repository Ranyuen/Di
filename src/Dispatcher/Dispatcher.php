<?php
/**
 * Annotation based simple DI & AOP at PHP.
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2015 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 * @link      https://github.com/Ranyuen/Di
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
    private $dispatcher;

    public function __construct(Container $c = null)
    {
        $this->c          = $c;
        $this->dispatcher = new NakedDispatcher($c);
    }

    public function __call($name, $args)
    {
        return call_user_func_array([$this->dispatcher, $name], $args);
    }

    /**
     * Dispatch the function.
     *
     * @param string|callable $func    Invokable.
     * @param array           $args    Arguments.
     * @param ojbect          $thisObj This of the method.
     *
     * @return mixed
     */
    public function invoke($func, array $args = [], $thisObj = null)
    {
        $func = $this->toCallable($func, $thisObj);
        return $this->dispatcher->invoke($func, $args);
    }

    /**
     * Reflect parameters or the function.
     *
     * @param string|callable $func    Invokable.
     * @param ojbect          $thisObj This of the method.
     *
     * @return \ReflectionParameter[]
     */
    public function getParameters($func, $thisObj = null)
    {
        $func = $this->toCallable($func, $thisObj);
        return $this->dispatcher->getParameters($func);
    }

    private function toCallable($func, $thisObj = null)
    {
        if (is_callable($func)) {
            return $func;
        }
        if (self::isRegex($func)) {
            return function ($subject, array &$matches = null, $flags = 0, $offset = 0) use ($func) {
                return preg_match($func, $subject, $matches, $flags, $offset);
            };
        }
        if (is_string($func) && (false !== strpos($func, '@'))) {
            list($interface, $method) = explode('@', $func);
            $invocation = function () use ($thisObj, $interface, $method) {
                if (!is_object($thisObj) || !($thisObj instanceof $interface)) {
                    if (isset($this->c[$interface])) {
                        $thisObj = $this->c[$interface];
                    } elseif ($thisObj = $this->c->getByType($interface)) {
                        null;
                    } else {
                        $thisObj = $this->c->newInstance($interface);
                    }
                }

                return call_user_func_array([$thisObj, $method], func_get_args());
            };
            $params = (new \ReflectionMethod($interface, $method))->getParameters();
            return new ParametrizedInvokable($invocation, $params);
        }
        throw new Exception('Not a callable: '.(string) $func);
    }
}
