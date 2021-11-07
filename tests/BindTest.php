<?php

require_once 'tests/Fixture/Momonga.php';

use Fixture\Momonga;
use Ranyuen\Di\Container;

class BindTest extends \PHPUnit\Framework\TestCase
{
    public function testBind()
    {
        $c = new Container();
        $c->bind('Fixture\Momonga', 'momonga', function ($c) {
            return new Momonga();
        });
        $this->assertInstanceOf('Fixture\Momonga', $c['momonga']);
        $this->assertSame($c['momonga'], $c['momonga']);
    }

    public function testFactory()
    {
        $c = new Container();
        $c->bind('Fixture\Momonga', 'momonga', $c->factory(function ($c) {
            return new Momonga();
        }));
        $this->assertInstanceOf('Fixture\Momonga', $c['momonga']);
        $this->assertNotSame($c['momonga'], $c['momonga']);
    }

    public function testProtect()
    {
        $c = new Container();
        $c['musasabi'] = function () {
            return 42;
        };
        $c['momonga'] = $c->protect(function () {
            return 42;
        });
        $this->assertEquals(42, $c['musasabi']);
        $this->assertEquals(42, $c['momonga']());
    }

    public function testExtend()
    {
        $c = new Container();
        $c['momonga'] = function ($c) {
            return 6;
        };
        $c->extend('momonga', function ($momonga, $c) {
            return $momonga * 7;
        });
        $this->assertEquals(42, $c['momonga']);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testFrozen()
    {
        $c = new Container();
        $c['momonga'] = function ($c) {
            return new Momonga();
        };
        $c['momonga'];
        $c->bind('Fixture\Momonga', 'momonga', function ($c) {
            return new Momonga();
        });
    }

    public function testNotFrozen()
    {
        $c = new Container();
        $c['momonga'] = $c->factory(function ($c) {
            return new Momonga();
        });
        $c['momonga'];
        $c->bind('Fixture\Momonga', 'momonga', function ($c) {
            return new Momonga();
        });

        $c = new Container();
        $c['momonga'] = 42;
        $c['momonga'];
        $c->bind('Fixture\Momonga', 'momonga', function ($c) {
            return new Momonga();
        });

        $this->assertTrue(true);
    }
}
