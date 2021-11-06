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

/**
 * Callable wich know getParameters().
 */
class ParametrizedInvokable
{
    /**
     * Invokable.
     *
     * @var callable
     */
    private $func;
    /**
     * Params.
     *
     * @var \ReflectionParameter[]
     */
    private $params;

    public function __construct(callable $func, array $params)
    {
        $this->func   = $func;
        $this->params = $params;
    }

    public function __invoke()
    {
        return call_user_func_array($this->func, func_get_args());
    }

    public function getParameters()
    {
        return $this->params;
    }
}
