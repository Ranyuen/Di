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

class ContainerTest extends PHPUnit_Framework_TestCase
{
    /** @var Container */
    private $_container;
    private $_momonga_id = 'iTyxdYeAnSP53tZq';

    public function __construct()
    {
        parent::__construct();
        $this->_container = new Container();
        $this->_container['cfg'] = function ($c) {
            return new Config();
        };
        $this->_container['num'] = function ($c) { return 42; };
        $this->_container->bind(
            'Fixture\Momonga',
            $this->_momonga_id, function ($c) {
                return new Momonga();
            }
        );
    }

    public function testInjectToConstructor()
    {
        $obj = $this->_container->newInstance(
            'Fixture\InjectToConstructor',
            ['arg1', 'arg2']
        );
        $this->assertEquals('arg1', $obj->arg1);
        $this->assertSame($this->_container['cfg'], $obj->cfg);
        $this->assertEquals($this->_container['num'], $obj->number);
        $this->assertEquals('arg2', $obj->arg2);
        $this->assertSame($this->_container[$this->_momonga_id], $obj->momonga);
    }

    public function testInjectToProperties()
    {
        $obj = $this->_container->newInstance(
            'Fixture\InjectToProperties',
            ['arg1', 'arg2']
        );
        $this->assertEquals('arg1', $obj->arg1);
        $this->assertSame($this->_container['cfg'], $obj->cfg);
        $this->assertEquals($this->_container['num'], $obj->number);
        $this->assertEquals('arg2', $obj->arg2);
        $this->assertSame($this->_container[$this->_momonga_id], $obj->momonga);
    }
}
