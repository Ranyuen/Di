<?php
namespace Fixture;

class InjectToProperties
{
    /** @var string */
    public $arg1;
    /**
     * @Inject
     * @var Fixture\Config
     */
    public $cfg;
    /**
     * @Inject
     * @Named("number=num")
     * @var integer
     */
    public $number;
    /** @var string */
    public $arg2;
    /**
     * @Inject
     * @var Fixture\Momonga
     */
    public $momonga;

    /**
     * @param string $arg1
     * @param string $arg2
     */
    public function __construct($arg1, $arg2)
    {
        $this->arg1 = $arg1;
        $this->arg2 = $arg2;
    }
}
