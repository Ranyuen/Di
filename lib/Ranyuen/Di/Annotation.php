<?php
namespace Ranyuen\Di;

class Annotation {
    /**
     * @param ReflectionMethod|ReflectionProperty $target
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
     * @param ReflectionMethod|ReflectionProperty $target
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
