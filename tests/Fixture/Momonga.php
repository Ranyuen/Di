<?php
namespace Fixture;

class Momonga
{
    /**
     * @var Fixture\Momonga
     *
     * @Inject
     * @Named("prop1=prop")
     */
    public $prop1;

    /** @Inject */
    public $injectAtFirstLine;

    /** @Named('1st=ok') */
    public $namedAtFirstLine;

    private $param1;
    private $param2;
    private $param3;

    /**
     * @param Fixture\Momonga $param2
     *
     * @Inject
     * @Named('param1=param')
     * @Named('param2=param,param3=param')
     */
    public function __construct(Momonga $param1 = null, $param2 = null, $param3 = null)
    {
        $this->param1 = $param1;
        $this->param2 = $param2;
        $this->param3 = $param3;
    }
}
