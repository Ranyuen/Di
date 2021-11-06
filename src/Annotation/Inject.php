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
        $vals = $this->getValues($target, 'Inject');

        return isset($vals[0]) ? $vals[0] : null;
    }
}
