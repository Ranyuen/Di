<?php

require_once 'tests/Fixture/Momonga.php';

use Fixture\Momonga;
use Ranyuen\Di\Container;

class GetByTypeTest extends PHPUnit_Framework_TestCase
{
    public function testGetByType()
    {
        $c = new Container();
        $this->assertNull($c->getByType('Fixture\Momonga'));
        $c->bind('Fixture\Momonga', 'momonga', function ($c) {
            return new Momonga();
        });
        $this->assertInstanceOf('Fixture\Momonga', $c->getByType('Fixture\Momonga'));
    }
}
