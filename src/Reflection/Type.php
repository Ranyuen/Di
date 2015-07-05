<?php
/**
 * Annotation based simple DI & AOP at PHP.
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2015 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 * @link      https://github.com/Ranyuen/Di
 */

namespace Ranyuen\Di\Reflection;

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
        if ($class = $param->getClass()) {
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
     * Inspired by https://github.com/mnapoli/PhpDocReader
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
        if ('\\' !== $type[0]) {
            $uses = $this->getUseStatements($class);
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
     * Parses a class.
     *
     * @param \ReflectionClass $class A <code>ReflectionClass</code> object.
     *
     * @return array A list with use statements in the form (Alias => FQN).
     */
    private function getUseStatements(\ReflectionClass $class)
    {
        if (false === ($filename = $class->getFilename())) {
            return [];
        }
        if (null === ($content = $this->getFileContent($filename, $class->getStartLine()))) {
            return [];
        }
        $namespace = preg_quote($class->getNamespaceName());
        $content = preg_replace('/^.*?(\bnamespace\s+'.$namespace.'\s*[;{].*)$/s', '\\1', $content);
        $uses = [];
        preg_match_all(
            '/^use\s+([a-zA-Z0-9_\\x7f-\\xff\\\\]+)(?:\s+as\s+([a-zA-Z0-9_\\x7f-\\xff]+))?\s*;/m',
            $content,
            $uses,
            PREG_SET_ORDER
        );
        return array_reduce(
            $uses,
            function ($uses, $item) {
                $ns = explode('\\', $item[1]);
                $uses[strtolower(end($ns))] = $item[1];
                if (isset($item[2])) {
                    $ns = explode('\\', $item[2]);
                    $uses[strtolower(end($ns))] = $item[2];
                }
                return $uses;
            },
            []
        );
    }

    /**
     * Gets the content of the file right up to the given line number.
     *
     * @param string  $filename   The name of the file to load.
     * @param integer $lineNumber The number of lines to read from file.
     *
     * @return string The content of the file.
     */
    private function getFileContent($filename, $lineNumber)
    {
        if (!is_file($filename)) {
            return null;
        }
        $content = '';
        $lineCnt = 0;
        $file = new \SplFileObject($filename);
        while (!$file->eof()) {
            if ($lineCnt++ == $lineNumber) {
                break;
            }
            $content .= $file->fgets();
        }
        return $content;
    }

    private function isTypeExists($type)
    {
        return class_exists($type) || interface_exists($type);
    }
}
