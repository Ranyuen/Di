<?php
namespace Fixture;

class InjectToConstructor
{
    /** @var string */
    public $arg1;
    /** @var Config */
    public $cfg;
    /** @var integer */
    public $num;
    /** @var string */
    public $arg2;
    /** @var Momonga */
    public $momonga;

    /**
     * @param string  $arg1
     * @param Config  $cfg
     * @param integer $number
     * @param string  $arg2
     * @param Momonga $momonga
     *
     * @Named("number=num")
     */
    public function __construct($arg1, Config $cfg, $number, $arg2, Momonga $momonga)
    {
        $this->arg1    = $arg1;
        $this->cfg     = $cfg;
        $this->number  = $number;
        $this->arg2    = $arg2;
        $this->momonga = $momonga;
    }
}
