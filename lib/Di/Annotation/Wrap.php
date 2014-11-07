<?php
/**
 * Annotation based simple DI (Dependency Injection) & AOP (Aspect Oriented Programming).
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2014 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 */
namespace Ranyuen\Di\Annotation;

/**
 * Wrap annotation.
 */
class Wrap extends Annotation
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
            if ($advices = $this->getWrap($method)) {
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
