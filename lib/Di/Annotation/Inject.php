<?php
/**
 * Annotation based simple DI & AOP at PHP.
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2015 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 */

namespace Ranyuen\Di\Annotation;

use Ranyuen\Di\Reflection\Annotation;

/**
 * Inject annotation.
 */
class Inject extends Annotation
{
    /**
     * Does the method or property has @Inject annotation?
     *
     * @param \ReflectionMethod|\ReflectionProperty $target Target.
     *
     * @return boolean
     */
    public function isInjectable($target)
    {
        return $this->hasAnnotation($target, 'Inject');
    }

    /**
     * Get @Inject value.
     *
     * @param \ReflectionProperty $target Target.
     *
     * @return string|null
     */
    public function getInject($target)
    {
        $values = $this->getValues($target, 'Inject');

        return isset($values[0]) ? $values[0] : null;
    }
}
