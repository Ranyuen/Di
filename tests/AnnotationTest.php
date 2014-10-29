<?php
require_once 'tests/res/AnnotationTestResource.php';

use Ranyuen\Di\Annotation;

class AnnotationTest extends PHPUnit_Framework_TestCase
{
    /** @var ReflectionMethod */
    private $constructor;
    /** @var ReflectionProperty */
    private $property;

    public function __construct()
    {
        parent::__construct();
        $interface = new ReflectionClass('\\AnnotationTestResource\\Momonga');
        $this->constructor = $interface->getMethod('__construct');
        $this->property = $interface->getProperty('prop1');
    }

    public function testIsInjectable()
    {
        $annotation = new Annotation();
        $this->assertTrue($annotation->isInjectable($this->constructor));
        $this->assertTrue($annotation->isInjectable($this->property));
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
    }
}
