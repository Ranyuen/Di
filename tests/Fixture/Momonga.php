<?php
namespace Fixture;

class Momonga
{
    /**
     * @Inject
     * @Named("prop1=prop")
     */
    public $prop1;

    private $param1;
    private $param2;
    private $param3;

    /**
     * @Inject
     * @Named('param1=param')
     * @Named('param2=param,param3=param')
     */
    public function __construct($param1 = null, $param2 = null, $param3 = null)
    {
        $this->param1 = $param1;
        $this->param2 = $param2;
        $this->param3 = $param3;
    }
}
