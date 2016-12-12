<?php

use fboes\SmallPhpHelpers\Tester;
use fboes\SmallPhpHelpers\Captcha;

/**
 * Autoloader ;)
 * @param $class
 */
function __autoload($class)
{
    require_once('../'.str_replace('fboes\SmallPhpHelpers', '', $class).'.php');
}

class CaptchaSimpleTest extends Tester
{
    public function testSimple()
    {
        $this->assertTrue(is_object(new Captcha\Simple('a')), 'CaptchaSimple is object');
    }

    public function testHtml()
    {
        $f = new Captcha\Simple('a');

        $output = $f->getHtml();
        $this->outputLine($output);

        $this->assertTrue(is_string($output), 'Expecting HTML output to be string');
        $this->assertValidHtml($output);
    }
}

CaptchaSimpleTest::doTest();
