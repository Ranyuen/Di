<?php
require 'tests/Fixture/MomongaAnnotation.php';

use Fixture\MomongaAnnotated;
use Fixture\MomongaAnnotation;

class CustomAnnotationTest extends PHPUnit_Framework_TestCase
{
    private $isNotMomnga;
    private $momonga1;
    private $momonga2;
    private $momonga3;

    public function __construct()
    {
        parent::__construct();
        $this->isNotMomonga = new \ReflectionMethod('Fixture\MomongaAnnotated', 'isNotMomonga');
        $this->momonga1     = new \ReflectionMethod('Fixture\MomongaAnnotated', 'momonga1');
        $this->momonga2     = new \ReflectionMethod('Fixture\MomongaAnnotated', 'momonga2');
        $this->momonga3     = new \ReflectionMethod('Fixture\MomongaAnnotated', 'momonga3');
    }

    public function testHasAnnotation()
    {
        $annotation = new MomongaAnnotation();
        $this->assertFalse($annotation->isMomonga($this->isNotMomonga));
        $this->assertTrue($annotation->isMomonga($this->momonga1));
        $this->assertTrue($annotation->isMomonga($this->momonga2));
        $this->assertTrue($annotation->isMomonga($this->momonga3));
    }

    public function testGetEachValue()
    {
        $annotation = new MomongaAnnotation();
        $this->assertNull($annotation->getEachMomonga($this->isNotMomonga));
        $this->assertEquals([], $annotation->getEachMomonga($this->momonga1));
        $this->assertEquals([], $annotation->getEachMomonga($this->momonga2));
        $this->assertEquals(
            [
                ['a',      'type' => 'sAcan', 'name' => ['sacchan']],
                ['b', 'c', 'type' => 'mikan', 'name' => ['ponkan'] ],
            ],
            $annotation->getEachMomonga($this->momonga3)
        );
    }

    public function testGetValues()
    {
        $annotation = new MomongaAnnotation();
        $this->assertNull($annotation->getEachMomonga($this->isNotMomonga));
        $this->assertEquals([], $annotation->getEachMomonga($this->momonga1));
        $this->assertEquals([], $annotation->getEachMomonga($this->momonga2));
        $this->assertEquals(
            ['a', 'b', 'c', 'type' => 'mikan', 'name' => ['ponkan']],
            $annotation->getMergedMomonga($this->momonga3)
        );
    }
}
