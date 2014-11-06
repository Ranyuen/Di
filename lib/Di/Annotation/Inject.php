<?php
/**
 * Annotation based simple DI (Dependency Injection) & AOP (Aspect Oriented Programming)
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2014 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 */
namespace Ranyuen\Di\Annotation;

/**
 * Inject annotation.
 */
class Inject
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
        return !!preg_match('/^[\\s\\/*]*@Inject\\W/m', $target->getDocComment());
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
        if (!preg_match('/^[\\s\\/*]*@Inject\\([\'"]([^\'"]+)[\'"]\\)/m', $target->getDocComment(), $matches)) {
            return null;
        }

        return $matches[1];
    }
}
