<?php
use fboes\SmallPhpHelpers\Tester;
use fboes\SmallPhpHelpers\Page;

/**
 * Autoloader ;)
 * @param $class
 */
function __autoload($class)
{
    require_once('../'.str_replace('fboes\SmallPhpHelpers', '', $class).'.php');
}

class PageTest extends Tester
{

    public function testSimple()
    {
        $social = new Page('Title of URL', 'http://example.com/test');

        $url = $social->facebookUrl();
        $this->assertTrue(is_string($url), 'URL is string');
        $this->outputLine($url);

        $url = $social->twitterUrl();
        $this->assertTrue(is_string($url), 'URL is string');
        $this->outputLine($url);

        $url = $social->emailUrl();
        $this->assertTrue(is_string($url), 'URL is string');
        $this->outputLine($url);

        $url = $social->printUrl();
        $this->assertTrue(is_string($url), 'URL is string');
        $this->outputLine($url);

        $url = $social->basicMeta();
        $this->assertTrue(is_string($url), 'Meta is string');
        $this->outputLine($url);

        $url = $social->opengraphMeta('http://example.com/image.jpg');
        $this->assertTrue(is_string($url), 'Meta is string');
        $this->outputLine($url);
    }
}

PageTest::doTest();
