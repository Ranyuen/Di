<?php

namespace Fixture;

use Ranyuen\Di\Reflection\AbstractAnnotation;

class MomongaAnnotation extends AbstractAnnotation
{
    public function isMomonga($target)
    {
        return $this->hasAnnotation($target, 'Momonga');
    }

    public function getEachMomonga($target)
    {
        return $this->getEachValue($target, 'Momonga');
    }

    public function getMergedMomonga($target)
    {
        return $this->getValues($target, 'Momonga');
    }
}

class MomongaAnnotated
{
    /**
     * @MomongaN
     */
    public function isNotMomonga()
    {
    }

    /**
     * @Momonga
     */
    public function momonga1()
    {
    }

    /**
     * @Momonga()
     */
    public function momonga2()
    {
    }

    /**
     * @Momonga (a,type=sAcan,name={sacchan})
     * @Momonga(
     *     b,
     *     type = mikan,
     *     name = { ponkan },
     *     c,
     * )
     */
    public function momonga3()
    {
    }
}
