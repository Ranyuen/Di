<?php
/**
 * Annotation based simple DI & AOP at PHP.
 *
 * @author    Ranyuen <cal_pone@ranyuen.com>
 * @author    ne_Sachirou <utakata.c4se@gmail.com>
 * @copyright 2014-2015 Ranyuen
 * @license   http://www.gnu.org/copyleft/gpl.html GPL
 */

namespace Ranyuen\Di\Reflection;

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
            '#^[\\s/*]*@'.preg_quote($annotation, '#').'(?=\W|$)#m',
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
        $doc = preg_replace('#^[\\s/*]*#m', '', $target->getDocComment());
        while (true) {
            $offset = 0;
            $matches = [];
            if (!preg_match(
                '#^@'.preg_quote($annotation, '#').'\\s*(?=\()#m',
                $doc,
                $matches,
                PREG_OFFSET_CAPTURE,
                $offset
            )) {
                return $values;
            }
            $offset = $matches[0][1] + strlen($matches[0][0]);
            $doc = substr($doc, $offset);
            $nValues = (new AnnotationParser($doc))->parse();
            if (is_null($nValues)) {
                continue;
            }
            $values = array_merge($values, $nValues);
        }
    }
}
