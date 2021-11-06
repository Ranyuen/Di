<?php

require_once 'tests/Fixture/Momonga.php';

use Fixture\Momonga;
use Ranyuen\Di\Annotation\Named;

class AnnotationNamedTest extends PHPUnit_Framework_TestCase
{
    /** @var Fixture\Momonga */
    private $interface;

    public function __construct()
    {
        parent::__construct();
        $this->interface = new ReflectionClass('Fixture\Momonga');
    }

    public function testGetNamed()
    {
        $this->assertEquals(
            ['param1' => 'param', 'param2' => 'param', 'param3' => 'param'],
            (new Named())->getNamed(
                $this->interface->getMethod('__construct')
            )
        );
        $this->assertEquals(
            ['prop1' => 'prop'],
            (new Named())->getNamed(
                $this->interface->getProperty('prop1')
            )
        );
        $this->assertEquals(
            ['1st' => 'ok'],
            (new Named())->getNamed(
                $this->interface->getProperty('namedAtFirstLine')
            )
        );
    }
}
