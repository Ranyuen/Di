<?php
require_once 'tests/Fixture/Config.php';
require_once 'tests/Fixture/InjectToConstructor.php';
require_once 'tests/Fixture/InjectToProperties.php';
require_once 'tests/Fixture/Momonga.php';

use Fixture\Config;
use Fixture\InjectToConstructor;
use Fixture\InjectToProperties;
use Fixture\Momonga;
use Ranyuen\Di\Container;

class InjectionTest extends PHPUnit_Framework_TestCase
{
    /** @var Container */
    private $container;
    private $momongaId = 'iTyxdYeAnSP53tZq';

    public function __construct()
    {
        parent::__construct();
        $this->container = new Container();
        $this->container['cfg'] = function ($c) {
            return new Config();
        };
        $this->container['num'] = function ($c) { return 42; };
        $this->container->bind(
            'Fixture\Momonga',
            $this->momongaId, function ($c) {
                return new Momonga();
            }
        );
    }

    public function testInjectToConstructor()
    {
        $obj = $this->container->newInstance(
            'Fixture\InjectToConstructor',
            ['arg1', 'arg2']
        );
        $this->assertEquals('arg1', $obj->arg1);
        $this->assertSame($this->container['cfg'], $obj->cfg);
        $this->assertEquals($this->container['num'], $obj->number);
        $this->assertEquals('arg2', $obj->arg2);
        $this->assertSame($this->container[$this->momongaId], $obj->momonga);
    }

    public function testInjectToProperties()
    {
        $obj = $this->container->newInstance(
            'Fixture\InjectToProperties',
            ['arg1', 'arg2']
        );
        $this->assertEquals('arg1', $obj->arg1);
        $this->assertSame($this->container['cfg'], $obj->cfg);
        $this->assertEquals($this->container['num'], $obj->number);
        $this->assertEquals($this->container['num'], $obj->number2);
        $this->assertEquals('arg2', $obj->arg2);
        $this->assertSame($this->container[$this->momongaId], $obj->momonga);
    }

    public function testInjectToNonObject()
    {
        $this->assertEquals(42, $this->container->inject(42));
    }
}
