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

namespace Ranyuen\Di\Annotation;

use Ranyuen\Di\Reflection\AbstractAnnotation;

/**
 * Named annotation.
 */
class Named extends AbstractAnnotation
{
    /**
     * Get the values of @Named annotation.
     *
     * @param \ReflectionMethod|\ReflectionProperty $target Target.
     *
     * @return array
     */
    public function getNamed($target)
    {
        return $this->getValues($target, 'Named');
    }
}
