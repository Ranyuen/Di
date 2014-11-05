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

    public function testGetInject()
    {
        $annotation = new Annotation();
        $this->assertEquals(
            '1st',
            $annotation->getInject(
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

    public function testGetTypeOfProperty()
    {
        $annotation = new Annotation();
        $this->assertEquals('Fixture\Momonga', $annotation->getType($this->property));
        $this->assertNull($annotation->getType($this->interface->getProperty('injectAtFirstLine')));
    }

    public function testGetTypeOfParameter()
    {
        $annotation = new Annotation();
        $params = $this->constructor->getParameters();
        $this->assertEquals('Fixture\Momonga', $annotation->getType($params[0]));
        $this->assertEquals('Fixture\Momonga', $annotation->getType($params[1]));
        $this->assertNull($annotation->getType($params[2]));
    }

    public function testGetFullNameOfType()
    {
        $annotation = new Annotation();
        $method = new ReflectionMethod('Ranyuen\Di\Annotation', 'getFullNameOfType');
        $method->setAccessible(true);
        $class = new ReflectionClass('AnnotationTest');
        $this->assertNull($method->invokeArgs($annotation, ['string', $class]));
        $this->assertNull($method->invokeArgs($annotation, ['a|b[]', $class]));
        $this->assertEquals(
            'F',
            $method->invokeArgs($annotation, ['\\F', $class])
        );
        $this->assertEquals(
            'Fixture\Momonga',
            $method->invokeArgs($annotation, ['Momonga', $class])
        );
        $this->assertEquals(
            'Fixture\Momonga',
            $method->invokeArgs($annotation, ['Fixture\Momonga', $class])
        );
    }
}
