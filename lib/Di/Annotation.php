<?php
/**
 * Simple Ray.Di style DI (Dependency Injector) extending Pimple.
 *
 * PHP version 5
 *
 * @category  Di
 * @package   Ranyuen\Di
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2014 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 * @version   GIT: 0.0.2
 * @link      https://github.com/Ranyuen/Di
 */
namespace Ranyuen\Di;

/**
 * Simple Ray.Di style DI (Dependency Injector) extending Pimple.
 *
 * @category Di
 * @package  Ranyuen\Di
 * @author   Ranyuen <cal_pone@ranyuen.com>
 * @author   ne_Sachirou <utakata.c4se@gmail.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GPL
 * @link     https://github.com/Ranyuen/Di
 */
class Annotation
{
    /**
     * Does the method or property has @Inject annotation?
     *
     * @param mixed $target ReflectionMethod|ReflectionProperty
     *
     * @return boolean
     */
    public function isInjectable($target)
    {
        return !!preg_match(
            '/^\\s*(?:\\/\\*)?\\*\\s*@Inject/m',
            $target->getDocComment()
        );
    }

    /**
     * Get the values of @Named annotation.
     *
     * @param mixed $target ReflectionMethod|ReflectionProperty
     *
     * @return array
     */
    public function getNamed($target)
    {
        $matches = [];
        $named = [];
        if (preg_match_all(
            '/^\\s*(?:\\/\\*)?\\*\\s*@Named\\([\'"]([^\'"]+)[\'"]\\)/m',
            $target->getDocComment(),
            $matches
        )) {
            foreach (explode(',', implode(',', $matches[1])) as $field) {
                $field = explode('=', $field);
                $named[$field[0]] = $field[1];
            }
        }

        return $named;
    }
}
