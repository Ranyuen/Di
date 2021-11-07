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

namespace Ranyuen\Di\Reflection;

use Doctrine\Common\Annotations\PhpParser;

/**
 * Type reflection tools.
 */
class Type
{
    /**
     * Get type name from type hinting or @var, @param annotation.
     *
     * @param \ReflectionProperty|\ReflectionParameter $target Target.
     *
     * @return string|null
     */
    public function getType($target)
    {
        if ($target instanceof \ReflectionParameter) {
            return $this->getTypeOfParameter($target);
        }

        return $this->getTypeOfProperty($target);
    }

    private function getTypeOfProperty(\ReflectionProperty $prop)
    {
        if (preg_match(
            '/^[\\s\\/*]*@var\\s+(\S+)/m',
            $prop->getDocComment(),
            $matches
        )) {
            return $this->getFullNameOfType(
                $matches[1],
                $prop->getDeclaringClass()
            );
        }

        return;
    }

    private function getTypeOfParameter(\ReflectionParameter $param)
    {
        $class = $param->getClass();
        if ($class) {
            return $class->name;
        }
        $paramName = $param->name;
        if (preg_match(
            '/^[\\s\\/*]*@param\\s+(\S+)\\s+\\$'.preg_quote($paramName, '/').'\\W/m',
            $param->getDeclaringFunction()->getDocComment(),
            $matches
        )) {
            return $this->getFullNameOfType(
                $matches[1],
                $param->getDeclaringFunction()->getDeclaringClass()
            );
        }

        return;
    }

    /**
     * Get Full name of the type.
     *
     * Inspired from https://github.com/mnapoli/PhpDocReader
     *
     * @param string           $type  Short type name.
     * @param \ReflectionClass $class Declared class.
     *
     * @return string|null
     */
    public function getFullNameOfType($type, $class)
    {
        $reserved = [
            'string',
            'int',
            'integer',
            'float',
            'bool',
            'boolean',
            'array',
            'resource',
            'null',
            'callable',
            'mixed',
            'void',
            'object',
            'false',
            'true',
            'self',
            'static',
            '$this',
        ];
        if (in_array($type, $reserved)
            || !preg_match('/^[a-zA-Z0-9_\\x7f-\\xff\\\\]+$/', $type)
        ) {
            return;
        }
        if ($type[0] !== '\\') {
            $uses = (new PhpParser())->parseClass($class);
            $alias = strtolower(explode('\\', $type)[0]);
            if (isset($uses[$alias])) {
                $type = $uses[$alias].preg_replace('/^[^\\\\]+/', '', $type);
            } elseif ($this->isTypeExists("{$class->getNamespaceName()}\\$type")) {
                $type = "{$class->getNamespaceName()}\\$type";
            } elseif (isset($uses['__NAMESPACE__']) && $this->isTypeExists("{$uses['__NAMESPACE__']}\\$type")) {
                $type = "{$uses['__NAMESPACE__']}\\$type";
            }
        }

        return ltrim($type, '\\');
    }

    private function isTypeExists($type)
    {
        return class_exists($type) || interface_exists($type);
    }
}
