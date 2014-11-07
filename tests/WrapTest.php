<?php
require_once 'tests/Fixture/Wrapped.php';

use Fixture\Wrapped;
use Ranyuen\Di\Container;

class WrapTest extends PHPUnit_Framework_TestCase
{
    public function testWrap()
    {
        $c = new Container();
        $c->wrap(
            'Fixture\Wrapped',
            ['inc'],
            function ($invocation, $args) {
                list($a, $w) = $args;
                ++$a;

                return $invocation($a, $w);
            }
        );
        $c->wrap(
            'Fixture\Wrapped',
            ['/^i/', '/^L/i'],
            function ($invocation, $args) {
                list($a, $w) = $args;
                ++$w->a;

                return $invocation($a, $w);
            }
        );
        $wrapped1 = $c->newInstance('Fixture\Wrapped');
        $this->assertTrue($wrapped1 instanceof Fixture\Wrapped);

        $wrapped2 = $c->newInstance('Fixture\Wrapped');
        $result = $wrapped1->inc(41, $wrapped2);
        $this->assertEquals(43, $result[0]->a);
        $this->assertEquals(42, $result[1]->a);
        $this->assertSame($wrapped2, $result[1]);

        $wrapped2 = $c->newInstance('Fixture\Wrapped');
        $result = $wrapped1->lnc(41, $wrapped2);
        $this->assertEquals(42, $result[0]->a);
        $this->assertEquals(42, $result[1]->a);
        $this->assertSame($wrapped2, $result[1]);
    }

    public function testWrappedByAnnotation()
    {
        $c = new Container();
        $c['q'] = $c->protect(
            function ($invocation, $args) {
                return $invocation($args[0] * 7);
            }
        );
        $wrapped = $c->newInstance('Fixture\Wrapped');
        $this->assertEquals(42, $wrapped->qqq(6));
    }
}
