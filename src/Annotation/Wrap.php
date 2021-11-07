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

namespace Ranyuen\Di\Annotation;

use Ranyuen\Di\Reflection\AbstractAnnotation;

/**
 * Wrap annotation.
 */
class Wrap extends AbstractAnnotation
{
    /**
     * Gather wraps.
     *
     * @param \ReflectionClass $target Target.
     *
     * @return array [advice => method[]]
     */
    public function gatherWraps($target)
    {
        $wraps = [];
        foreach ($target->getMethods() as $method) {
            $advices = $this->getWrap($method);
            if ($advices) {
                $name = $method->getName();
                foreach ($advices as $advice) {
                    $wraps[$advice][] = $name;
                }
            }
        }

        return $wraps;
    }

    /**
     * Get wrap.
     *
     * @param \ReflectionMethod $target Target.
     *
     * @return array
     */
    public function getWrap($target)
    {
        return $this->getValues($target, 'Wrap');
    }
}
