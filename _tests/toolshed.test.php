<?php

require('../Tester.php');
require('../toolshed.php');

class toolshedTest extends Tester {
	public function testSimple () {
		$this->assertFunctionExists('_');
		$this->assertFunctionExists('_echo');
		$this->assertFunctionExists('_paragraph');
	}


	public function dataParagraph () {
		return array(
			'Simple' => array('Kleiner testparagraph' , '<p>Kleiner testparagraph</p>'),
			'Quoting HTML' => array('<a href="">Test</a>' , '<p>&lt;a href=&quot;&quot;&gt;Test&lt;/a&gt;</p>'),
			'Line Breaks' => array("Noch ein kleiner\n Paragraph", "<p>Noch ein kleiner<br />\n Paragraph</p>"),
			'More line Breaks' => array("Noch ein kleiner\n\nParagraph", "<p>Noch ein kleiner</p>\n<p>Paragraph</p>"),
			'Complete gibberish' => array('ü+ö#ö# <>>> "" 6376783 <script >')
		);
	}

	public function testParagraph ($paragraph, $expectedResult = NULL) {
		$output = _paragraph($paragraph);
		#$this->outputLine($output);
		$this->assertValidHtml($output);
		if (!is_blank($expectedResult)){
			$this->assertEquals($output, $expectedResult);
		}
	}

	public function testSprint () {
		$this->assertFunctionExists('_sprintf');
		$this->assertFunctionExists('_vsprintf');
	}

	public function testMakeId () {
		$this->assertEquals(make_id('a'), 'a');
		$this->assertEquals(make_id('abcd'), 'abcd');
		$this->assertEquals(make_id('1abcd'), 'id_1abcd');
		$this->assertEquals(make_id('1ü Lumm Plopp'), 'id_1_Lumm_Plopp');
	}

	public function testIsBlank () {
		$this->assertFunctionExists('is_blank');

		$this->assertTrue(is_blank($a),'Expecting is_blank on undefined being TRUE');
		$a = NULL;
		$this->assertTrue(is_blank($a),'Expecting is_blank on NULL being TRUE');
		$a = '';
		$this->assertTrue(is_blank($a),'Expecting is_blank on empty string being TRUE');
		$a = array();
		$this->assertTrue(is_blank($a),'Expecting is_blank on empty array being TRUE');
		$this->assertTrue(is_blank($a['test']),'Expecting is_blank on empty array -key being TRUE');
		$this->assertTrue(is_blank($a['test']),'Expecting is_blank on empty array -key being TRUE a second time');
		#$this->outputLine($a);

		$a = 0;
		$this->assertTrue(empty($a));
		$this->assertTrue(!is_blank($a), 'Expecting is_blank on 0 to be FALSE');

		$a = 'a';
		$this->assertTrue(!is_blank($a), 'Expecting is_blank on not empty string to be FALSE');
		$a = array(1);
		$this->assertTrue(!is_blank($a), 'Expecting is_blank on not empty array to be FALSE');
	}

	public function testGetValue () {
		$this->assertFunctionExists('get_value');

		$this->assertEquals(get_value($a), NULL);
		$this->assertEquals(get_value($a, array()), array());
		$this->assertEquals(get_value($a, 'x'), 'x');

		$a = 0;
		$this->assertEquals(get_value($a), $a);
		$this->assertEquals(get_value($a, 'x'), $a);

		$a = 'bla';
		$this->assertEquals(get_value($a), $a);
		$this->assertEquals(get_value($a, 'x'), $a);

	}

	public function dataMakeArray () {
		return array(
			'Single line' => array('a', array('a')),
			'Multiple line' => array('a
b', array('a','b')),
			'Single line, associative' => array('a:b', array('a' => 'b')),
			'Multiple line, associative' => array('a:b
c : d', array('a' => 'b', 'c' => 'd')),
		);
	}

	public function testMakeArray ($input, array $output) {
		$array = make_array($input);
		$this->assertTrue(is_array($array), 'Expecting make_array to return an array');
		$this->assertEquals($array,$output);
	}

	public function testSafe_filename () {
		$this->assertEquals(safe_filename('bla.txt',''),'bla.txt');
		$this->assertEquals(safe_filename('blubb/bla.txt',''),'bla.txt');
		$this->assertEquals(safe_filename('blubb/bla.txt','','#\.txt$#'),'bla.txt');
		$this->assertEquals(safe_filename('blubb/bla.txt','','#\.doc$#'),NULL);
		$this->assertEquals(safe_filename('bla.txt','blubb'),'blubb/bla.txt');
		$this->assertEquals(safe_filename('dingo/bla.txt','blubb'),'blubb/bla.txt');
		$this->assertEquals(safe_filename('dingo/bla.txt','/home/user/test'),'/home/user/test/bla.txt');
		$this->assertEquals(safe_filename('dingo/bla.txt','/home/user/test/'),'/home/user/test/bla.txt');
	}

	public function testTranslation () {
		$test       = 'I am legend';
		activate_translations('de','DE');
		$translated = _('I am legend');

		$this->outputLine($translated);
		$this->assertTrue($translated != $test, 'Expecting translated string to be different from original string');

		$locale = find_best_locale();
		$this->assertTrue(is_array($locale), 'Expecting locale to be an array');
	}
}

toolshedTest::doTest();
