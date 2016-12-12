<?php
use fboes\SmallPhpHelpers\Tester;
use fboes\SmallPhpHelpers\XmlEncode;

/**
 * Autoloader ;)
 * @param $class
 */
function __autoload($class)
{
    require_once('../'.str_replace('fboes\SmallPhpHelpers', '', $class).'.php');
}

class XmlEncodeTest extends Tester
{
    public function testSimple()
    {
        $this->assertTrue(is_object(new XmlEncode('a')), 'XmlEncode is object');
        $this->assertClassHasAttribute('data', 'fboes\SmallPhpHelpers\XmlEncode');
    }

    public function dataXml()
    {
        return array (
            'string' => array('a'),
            'array' => array(array(1,2,3)),
            'object' => array((object)array('a' => 'b')),
            'self' => array($this),
        );
    }

    public function testXml($a)
    {
        $obj = new XmlEncode($a);
        $output = $obj->output();

        $this->outputLine($output);
        $this->assertValidXml($output);
    }
}

XmlEncodeTest::doTest();
