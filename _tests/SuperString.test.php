<?php
use fboes\SmallPhpHelpers\Tester;
use fboes\SmallPhpHelpers\SuperString;

/**
 * Autoloader ;)
 * @param $class
 */
function __autoload($class)
{
    require_once('../'.str_replace('fboes\SmallPhpHelpers', '', $class).'.php');
}

class StringTest extends Tester
{
    public function testSimple()
    {
        $this->assertTrue(is_object(new SuperString('X')), 'String is object');
        $this->assertClassHasAttribute('string', 'fboes\SmallPhpHelpers\SuperString');
    }

    public function testChaining()
    {
        $test = 'I am a lengthy test and chain all methods for testing.';
        $string = (string)SuperString::init($test)
            ->paragraph()
            ->textile()
            ->markdown()
            ->addingTubes()
            ->sprintf('x')
            ->vsprintf(array('x'))
            ->htmlallchars()
            ->strShorten()
            ->str_nice_shorten()
            ->asciify()
            ->make_id()
            ->improve_typography()
            ->externalLinks()
        ;
        $this->outputLine($string);
        $this->assertTrue(is_string($string), 'String is string');
        $this->assertTrue(!empty($string), 'String is not empty');
    }

    public function dataMarkdown()
    {
        return array(
            'Simple' => array(
                'X... Y 2x2 "xx" *test*',
                '<p>X… Y 2×2 &quot;xx&quot; <strong>test</strong></p>'
            ),
            'Table' => array(
                "|1|2|\n|3|4|",
                "<table><tr><td>1</td><td>2</td></tr>\n<tr><td>3</td><td>4</td></tr></table>"
            )
        );
    }

    public function testMarkdown($test, $result)
    {
        $string = (string)SuperString::init($test)->improve_typography()->markdown();
        $this->outputLine($string);
        $this->assertTrue(is_string($string), 'String is string');
        $this->assertTrue(!empty($string), 'String is not empty');
        $this->assertTrue($string !== $test, 'String is not in its original state');
        $this->assertTrue($string === $result, 'String is expected result');
        $this->assertValidHtml($string);
    }

    public function testNormalize()
    {
        $test = '1. IDs füngen nie mit Züffern an!';
        $string = (string)SuperString::init($test)->make_id();
        $this->outputLine($string);
        $this->assertTrue(is_string($string), 'String is string');
        $this->assertTrue(!empty($string), 'String is not empty');
        $this->assertTrue($string !== $test, 'String is not in its original state');

        $string = (string)SuperString::init($test)->asciify();
        $this->outputLine($string);
        $this->assertTrue(is_string($string), 'String is string');
        $this->assertTrue(!empty($string), 'String is not empty');
        $this->assertTrue($string !== $test, 'String is not in its original state');
    }
}

StringTest::doTest();
