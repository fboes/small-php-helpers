<?php

require('../Tester.class.php');
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
}

toolshedTest::doTest();
