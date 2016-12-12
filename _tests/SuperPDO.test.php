<?php
use fboes\SmallPhpHelpers\Tester;
use fboes\SmallPhpHelpers\SuperPDO;

/**
 * Autoloader ;)
 * @param $class
 */
function __autoload($class)
{
    require_once('../'.str_replace('fboes\SmallPhpHelpers', '', $class).'.php');
}

class SuperPDOTest extends Tester {
    public function testSimple () {
    }

}

SuperPDOTest::doTest();
