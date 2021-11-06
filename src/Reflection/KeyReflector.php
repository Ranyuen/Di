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

use Ranyuen\Di\Annotation\Inject;
use Ranyuen\Di\Annotation\Named;
use Ranyuen\Di\Container;

/**
 * Detect Container's key from Reflection object.
 */
class KeyReflector
{
    private $c;

    public function __construct(Container $c)
    {
        $this->c = $c;
    }

    /**
     * Detect what key to get the value.
     *
     * Priority.
     * 1. Inject annotation with name.
     * 2. Named annotation.
     * 3. Type hinting and type of var annotation.
     * 4. Variable name.
     *
     * @param \ReflectionParameter|\ReflectionProperty $obj Target.
     *
     * @return string
     */
    public function detectKey(\Reflector $obj)
    {
        $key = $obj->name;
        if ($obj instanceof \ReflectionProperty) {
            if ($injectName = (new Inject())->getInject($obj)) {
                $named = [$key => $injectName];
            } else {
                $named = (new Named())->getNamed($obj);
            }
        } else {
            $named = (new Named())->getNamed($obj->getDeclaringFunction());
        }
        if (isset($named[$key])) {
            $key = $named[$key];
        } elseif (($type = (new Type())->getType($obj))
            && isset($this->c->classes[$type])
        ) {
            $key = $this->c->classes[$type];
        }

        return $key;
    }
}
