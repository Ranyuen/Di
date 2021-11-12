<?php

require_once 'tests/Fixture/Lighter.php';

use Ranyuen\Di\Container;

class FacadeTest extends \PHPUnit\Framework\TestCase
{
    public function testBasicFacade()
    {
        $c = new Container();
        $c->bind('Fixture\Lighter', 'lighter', function ($c) {
            return new Fixture\Lighter();
        });
        $c->facade('OilLighter', 'lighter');
        Container::setAsFacade($c);
        $goods = OilLighter::candle('Urushi', 'Sinjyu');
        $this->assertEquals('Sinjyu top of the Urushi', $goods);
    }
}
