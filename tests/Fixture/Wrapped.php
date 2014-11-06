<?php
namespace Fixture;

class Wrapped
{
    /** @var integer */
    public $a = 41;

    /**
     * @param integer $a
     * @param Wrapped $w
     *
     * @return Wrapped[]
     */
    public function inc($a = 41, Wrapped $w = null)
    {
        $this->a = $a + 1;

        return [$this, $w];
    }

    public function lnc($a = 41, Wrapped $w = null)
    {
        $this->a = $a + 1;

        return [$this, $w];
    }
}
