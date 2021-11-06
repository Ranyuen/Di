<?php

require_once 'tests/Fixture/Momonga.php';
require_once 'tests/Fixture/dispatcherCallables.php';

use Ranyuen\Di\Container;
use Ranyuen\Di\Dispatcher\Dispatcher;

class DispatcherTest extends PHPUnit_Framework_TestCase
{
    private function getInjector()
    {
        $c = new Container();
        $c->bind('Fixture\Momonga', 'momonga', function ($c) {
            return new Fixture\Momonga();
        });

        return new Dispatcher($c);
    }

    public function testIsRegex()
    {
        $isRegex = (new ReflectionMethod('Ranyuen\Di\Dispatcher\Dispatcher', 'isRegex'))->getClosure();
        $regex = '/rEGeX/i';
        $this->assertTrue($isRegex($regex));
        $regex = '#rEGeX#i';
        $this->assertTrue($isRegex($regex));
        $regex = '(rEGeX)i';
        $this->assertTrue($isRegex($regex));
        $regex = '{rEGeX}i';
        $this->assertTrue($isRegex($regex));
        $regex = '[rEGeX]i';
        $this->assertTrue($isRegex($regex));
        $regex = '<rEGeX>i';
        $this->assertTrue($isRegex($regex));
    }

    public function testInvokeFunction()
    {
        $injector = $this->getInjector();
        $injector->invoke('Fixture\dpFunc', [$this]);
    }

    public function testInvokeClosure()
    {
        $injector = $this->getInjector();
        $injector->invoke(Fixture\getDpClosure(), [$this]);
    }

    public function testInvokeStaticArr()
    {
        $injector = $this->getInjector();
        $injector->invoke(['Fixture\DpClass', 'dpStatic'], [$this]);
    }

    public function testInvokeStaticStr()
    {
        $injector = $this->getInjector();
        $injector->invoke('Fixture\DpClass::dpStatic', [$this]);
    }

    public function testInvokeMethodArr()
    {
        $injector = $this->getInjector();
        $injector->invoke([new Fixture\DpClass(), 'dpMethod'], [$this]);
    }

    public function testInvokeMethodStr()
    {
        $injector = $this->getInjector();
        $injector->invoke('Fixture\DpClass@dpMethod', [$this], new Fixture\DpClass());
    }
}
