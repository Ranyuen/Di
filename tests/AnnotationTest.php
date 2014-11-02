<?php
require_once 'tests/Fixture/Momonga.php';

use Fixture\Momonga;
use Ranyuen\Di\Annotation;

class AnnotationTest extends PHPUnit_Framework_TestCase
{
    /** @var Fixture\Momonga */
    private $interface;
    /** @var ReflectionMethod */
    private $constructor;
    /** @var ReflectionProperty */
    private $property;

    public function __construct()
    {
        parent::__construct();
        $this->interface = new ReflectionClass('Fixture\Momonga');
        $this->constructor = $this->interface->getMethod('__construct');
        $this->property = $this->interface->getProperty('prop1');
    }

    public function testIsInjectable()
    {
        $annotation = new Annotation();
        $this->assertTrue($annotation->isInjectable($this->constructor));
        $this->assertTrue($annotation->isInjectable($this->property));
        $this->assertTrue(
            $annotation->isInjectable(
                $this->interface->getProperty('injectAtFirstLine')
            )
        );
    }

    public function testGetNamed()
    {
        $annotation = new Annotation();
        $this->assertEquals(
            ['param1' => 'param', 'param2' => 'param', 'param3' => 'param'],
            $annotation->getNamed($this->constructor)
        );
        $this->assertEquals(
            ['prop1' => 'prop'],
            $annotation->getNamed($this->property)
        );
        $this->assertEquals(
            ['1st' => 'ok'],
            $annotation->getNamed($this->interface->getProperty('namedAtFirstLine'))
        );
    }
}
