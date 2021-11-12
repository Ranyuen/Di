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

declare(strict_types=1);

namespace Ranyuen\Di\Reflection;

/**
 * Annotation base.
 */
abstract class AbstractAnnotation
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
        if (! is_callable([$target, 'getDocComment'])) {
            throw new AnnotationException('Not annotatalbe: '. (string) $target);
        }

        return ! ! preg_match(
            '#^[\\s/*]*@'.preg_quote($annotation, '#').'(?=\W|$)#m',
            strval($target->getDocComment())
        );
    }

    /**
     * Get the annotation values. Keep separated each annotations.
     *
     * @param mixed  $target     Target.
     * @param string $annotation Annotation name.
     *
     * @return array[]
     *
     * @throws AnnotationException The target isn't reflectable.
     */
    protected function getEachValue($target, $annotation)
    {
        if (! $this->hasAnnotation($target, $annotation)) {
            return null;
        }
        $vals = [];
        $doc = preg_replace('#^[\\s/*]*#m', '', $target->getDocComment());
        while (true) {
            $offset = 0;
            $matches = [];
            if (! preg_match(
                '#^@'.preg_quote($annotation, '#').'\\s*(?=\()#m',
                $doc,
                $matches,
                PREG_OFFSET_CAPTURE,
                $offset
            )
            ) {
                return $vals;
            }
            $offset = $matches[0][1] + strlen($matches[0][0]);
            $doc = substr($doc, $offset);
            $nValues = (new AnnotationParser($doc))->parse();
            if (! is_null($nValues)) {
                $vals[] = $nValues;
            }
        }
    }

    /**
     * Get the annotation values. Merge each annotations.
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
        if (! $this->hasAnnotation($target, $annotation)) {
            return null;
        }
        return array_reduce(
            $this->getEachValue($target, $annotation),
            function ($carry, $val) {
                return array_merge($carry, $val);
            },
            []
        );
    }
}
