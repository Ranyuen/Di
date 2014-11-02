<?php
/**
 * Simple Ray.Di style DI (Dependency Injector) extending Pimple.
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2014 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 */
namespace Ranyuen\Di;

/**
 * Inject annotation.
 */
class Annotation
{
    /**
     * Does the method or property has @Inject annotation?
     *
     * @param ReflectionMethod|ReflectionProperty $target Target.
     *
     * @return boolean
     */
    public function isInjectable($target)
    {
        return !!preg_match('/^[\\s\\/*]*@Inject\W/m', $target->getDocComment());
    }

    /**
     * Get the values of @Named annotation.
     *
     * @param ReflectionMethod|ReflectionProperty $target Target.
     *
     * @return array
     */
    public function getNamed($target)
    {
        $named = [];
        $matches = [];
        if (preg_match_all(
            '/^[\\s\\/*]*@Named\\([\'"]([^\'"]+)[\'"]\\)/m',
            $target->getDocComment(),
            $matches
        )) {
            foreach (explode(',', implode(',', $matches[1])) as $field) {
                $field = explode('=', $field);
                $named[trim($field[0])] = trim($field[1]);
            }
        }

        return $named;
    }
}
