<?php
require_once 'tests/res/AnnotationTestResource.php';

use \Ranyuen\Di\Annotation;

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
        $this->property = $interface->getProperty('p1');
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
            ['p1' => 'param', 'p2' => 'param', 'p3' => 'param'],
            $annotation->getNamed($this->constructor)
        );
        $this->assertEquals(
            ['p1' => 'prop'],
            $annotation->getNamed($this->property)
        );
    }
}
