<?php
use fboes\SmallPhpHelpers\Tester;
use fboes\SmallPhpHelpers\Messages;

/**
 * Autoloader ;)
 * @param $class
 */
function __autoload($class)
{
    require_once('../'.str_replace('fboes\SmallPhpHelpers', '', $class).'.php');
}
class MessagesTest extends Tester {
    public function testSimple () {
        $this->assertTrue(is_object(new Messages()), 'Messages is object');
    }

}

MessagesTest::doTest();
