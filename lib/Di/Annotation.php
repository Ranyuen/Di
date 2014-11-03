<?php
/**
 * Annotation based, simple DI (Dependency Injector) extending Pimple.
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2014 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 */
namespace Ranyuen\Di;

use ReflectionParameter;

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
        return !!preg_match('/^[\\s\\/*]*@Inject\\W/m', $target->getDocComment());
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

    /**
     * Get type name from type hinting or @var, @param annotation.
     *
     * @param ReflectionProperty|ReflectionParameter $target Target.
     *
     * @return string|null
     */
    public function getType($target)
    {
        if ($target instanceof ReflectionParameter) {
            return $this->getTypeOfParameter($target);
        }

        return $this->getTypeOfProperty($target);
    }

    /**
     * @param ReflectionProperty $prop Target property.
     *
     * @return string|null
     */
    private function getTypeOfProperty($prop)
    {
        $matches = [];
        if (preg_match(
            '/^[\\s\\/*]*@var\\s+([a-zA-Z0-9_\\x7f-\\xff\\\\]+)/m',
            $prop->getDocComment(),
            $matches
        )) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @param ReflectionParameter $param Target parameter.
     *
     * @return string|null
     */
    private function getTypeOfParameter($param)
    {
        $class = $param->getClass();
        if ($class) {
            return $class->getName();
        }
        $paramName = $param->getName();
        $matches = [];
        if (preg_match(
            "/^[\\s\\/*]*@param\\s+([a-zA-Z0-9_\\x7f-\\xff\\\\]+)\\s+\\$$paramName\\W/m",
            $param->getDeclaringFunction()->getDocComment(),
            $matches
        )) {
            return $matches[1];
        }

        return null;
    }
}
