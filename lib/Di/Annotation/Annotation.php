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
 * Annotation base.
 */
abstract class Annotation
{
    /**
     * Does the target has the annotations?
     *
     * @param mixed  $target     Target.
     * @param string $annotation Annotation name.
     *
     * @return boolean
     *
     * @throws AnnotationException The target isn't reflectable.
     */
    protected function hasAnnotation($target, $annotation)
    {
        if (!is_callable([$target, 'getDocComment'])) {
            throw new AnnotationException();
        }

        return !!preg_match(
            "/^[\\s\\/*]*@$annotation(?:\W|$)/m",
            $target->getDocComment()
        );
    }

    /**
     * Get the annotation values.
     *
     * @param mixed  $target     Target.
     * @param string $annotation Annotation name.
     *
     * @return array
     *
     * @throws AnnotationException The target isn't reflectable.
     */
    protected function getValues($target, $annotation)
    {
        if (!is_callable([$target, 'getDocComment'])) {
            throw new AnnotationException();
        }
        $values = [];
        if (preg_match_all(
            "/^[\\s\\/*]*@$annotation(?:\\((?:['\"]([^'\"]+)['\"])?\\))?/m",
            $target->getDocComment(),
            $matches
        )) {
            foreach (explode(',', implode(',', $matches[1])) as $field) {
                if (false === strpos($field, '=')) {
                    $values[] = trim($field);
                } else {
                    $field = explode('=', $field);
                    $values[trim($field[0])] = trim($field[1]);
                }
            }
        }

        return $values;
    }
}
