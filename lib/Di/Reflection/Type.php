<?php
/**
 * Annotation based simple DI (Dependency Injection) & AOP (Aspect Oriented Programming).
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2014 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
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

    /**
     * @param \ReflectionProperty $prop Target property.
     *
     * @return string|null
     */
    private function getTypeOfProperty($prop)
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

        return null;
    }

    /**
     * @param \ReflectionParameter $param Target parameter.
     *
     * @return string|null
     */
    private function getTypeOfParameter($param)
    {
        if ($class = $param->getClass()) {
            return $class->getName();
        }
        $paramName = $param->getName();
        if (preg_match(
            "/^[\\s\\/*]*@param\\s+(\S+)\\s+\\$$paramName\\W/m",
            $param->getDeclaringFunction()->getDocComment(),
            $matches
        )) {
            return $this->getFullNameOfType(
                $matches[1],
                $param->getDeclaringFunction()->getDeclaringClass()
            );
        }

        return null;
    }

    /**
     * Get Full name of the type.
     *
     * https://github.com/mnapoli/PhpDocReader
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
            return null;
        }
        if ('\\' !== $type[0]) {
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

    /**
     * @param string $type Class or interface name.
     *
     * @return boolean
     */
    private function isTypeExists($type)
    {
        return class_exists($type) || interface_exists($type);
    }
}
