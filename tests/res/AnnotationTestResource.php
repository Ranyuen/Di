<?php
namespace AnnotationTestResource;

class AnnotationTestResource
{
}

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
    public function __construct($param1, $param2, $param3)
    {
        $this->param1 = $param1;
        $this->param2 = $param2;
        $this->param3 = $param3;
    }
}
