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
 * Named annotation.
 */
class Named extends Annotation
{
    /**
     * Get the values of @Named annotation.
     *
     * @param \ReflectionMethod|\ReflectionProperty $target Target.
     *
     * @return array
     */
    public function getNamed($target)
    {
        return $this->getValues($target, 'Named');
    }
}
