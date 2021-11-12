<?php

require_once 'tests/Fixture/Momonga.php';

use Fixture\Momonga;
use Ranyuen\Di\Reflection\Type;

class ReflectionTypeTest extends \PHPUnit\Framework\TestCase
{
    /** @var Fixture\Momonga */
    private $interface;

    public function __construct()
    {
        parent::__construct();
        $this->interface = new ReflectionClass('Fixture\Momonga');
    }

    public function testGetTypeOfProperty()
    {
        $this->assertEquals('Fixture\Momonga', (new Type())->getType($this->interface->getProperty('prop1')));
        $this->assertNull((new Type())->getType($this->interface->getProperty('injectAtFirstLine')));
    }

    public function testGetTypeOfParameter()
    {
        $params = $this->interface->getMethod('__construct')->getParameters();
        $this->assertEquals('Fixture\Momonga', (new Type())->getType($params[0]));
        $this->assertEquals('Fixture\Momonga', (new Type())->getType($params[1]));
        $this->assertNull((new Type())->getType($params[2]));
    }

    public function testGetFullNameOfType()
    {
        $class = new ReflectionClass('ReflectionTypeTest');
        $this->assertNull((new Type())->getFullNameOfType('string', $class));
        $this->assertNull((new Type())->getFullNameOfType('a|b[]', $class));
        $this->assertEquals(
            'F',
            (new Type())->getFullNameOfType('\\F', $class)
        );
        $this->assertEquals(
            'Fixture\Momonga',
            (new Type())->getFullNameOfType('Momonga', $class)
        );
        $this->assertEquals(
            'Fixture\Momonga',
            (new Type())->getFullNameOfType('Fixture\Momonga', $class)
        );
    }
}
