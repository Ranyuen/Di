<?php

class ReadmeTest extends \PHPUnit\Framework\TestCase
{
    public function testReadme()
    {
        ob_start();
        eval('?>'.file_get_contents('README.md'));
        $result = ob_get_clean();
        $this->assertRegExp('/bool\(true\)/', $result);
        $this->assertFalse(strpos($result, 'bool(false)'));
    }
}
