<?php

require_once 'tests/Fixture/Momonga.php';

use Fixture\Momonga;
use Ranyuen\Di\Annotation\Inject;

class AnnotationInjectTest extends PHPUnit_Framework_TestCase
{
    /** @var Fixture\Momonga */
    private $interface;

    public function __construct()
    {
        parent::__construct();
        $this->interface = new ReflectionClass('Fixture\Momonga');
    }

    public function testIsInjectable()
    {
        $this->assertTrue(
            (new Inject())->isInjectable(
                $this->interface->getMethod('__construct')
            )
        );
        $this->assertTrue(
            (new Inject())->isInjectable(
                $this->interface->getProperty('prop1')
            )
        );
        $this->assertFalse(
            (new Inject())->isInjectable(
                $this->interface->getProperty('prop2')
            )
        );
        $this->assertTrue(
            (new Inject())->isInjectable(
                $this->interface->getProperty('injectAtFirstLine')
            )
        );
    }

    public function testGetInject()
    {
        $this->assertEquals(
            '1st',
            (new Inject())->getInject(
                $this->interface->getProperty('injectAtFirstLine')
            )
        );
    }
}
