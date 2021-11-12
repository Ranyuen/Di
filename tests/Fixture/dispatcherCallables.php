<?php

namespace Fixture;

function dpFunc($test, $momonga, Momonga $m)
{
    $test->assertInstanceOf('Fixture\Momonga', $momonga);
    $test->assertInstanceOf('Fixture\Momonga', $m);
}

function getDpClosure()
{
    return function ($test, $momonga, Momonga $m) {
        $test->assertInstanceOf('Fixture\Momonga', $momonga);
        $test->assertInstanceOf('Fixture\Momonga', $m);
    };
}

class DpClass
{
    public static function dpStatic($test, $momonga, Momonga $m)
    {
        $test->assertInstanceOf('Fixture\Momonga', $momonga);
        $test->assertInstanceOf('Fixture\Momonga', $m);
    }

    public function dpMethod($test, $momonga, Momonga $m)
    {
        $test->assertInstanceOf('Fixture\Momonga', $momonga);
        $test->assertInstanceOf('Fixture\Momonga', $m);
    }
}
