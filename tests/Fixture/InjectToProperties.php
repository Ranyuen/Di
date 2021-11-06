<?php

namespace Fixture;

class InjectToProperties
{
    /** @var string */
    public $arg1;
    /**
     * Inject with type
     *
     * @Inject
     * @var Config
     */
    public $cfg;
    /**
     * Inject with named
     *
     * @Inject
     * @Named(number=num)
     * @var integer
     */
    public $number;
    /**
     * Inject with @Inject name
     *
     * @Inject(num)
     */
    public $number2;
    /** @var string */
    public $arg2;
    /**
     * Inject with name
     *
     * @Inject
     * @var Momonga
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
