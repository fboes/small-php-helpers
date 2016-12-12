<?php
use fboes\SmallPhpHelpers\Tester;
use fboes\SmallPhpHelpers\HtmlEncode;

/**
 * Autoloader ;)
 * @param $class
 */
function __autoload($class)
{
    require_once('../'.str_replace('fboes\SmallPhpHelpers', '', $class).'.php');
}

class HtmlEncodeTest extends Tester
{
    public function testSimple()
    {
        $this->assertTrue(is_object(new HtmlEncode('a')), 'HtmlEncode is object');
        $this->assertClassHasAttribute('data', 'fboes\SmallPhpHelpers\HtmlEncode');
    }

    public function dataHtml()
    {
        return array (
            'string' => array('a'),
            'array' => array(array(1, 2, 3)),
            'object' => array((object)array('a' => 'b')),
            'self' => array($this),
        );
    }

    public function testHtml($a)
    {
        $obj = new HtmlEncode($a);
        $this->assertValidHtml($obj->output());
    }
}

HtmlEncodeTest::doTest();
