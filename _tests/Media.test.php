<?php

require('../Tester.php');
require('../Media.php');
require('../Media/Audio.php');
require('../Media/Video.php');

class MediaTest extends Tester {
	public function testMedia () {
		$m = Media::dimensions(320,480)
			->setFallbackText('test')
			->addMedia('test.txt')
			->addMedia('another-test.xml')
			->setPosterImage('test.jpg')
		;

		$this->assertTrue(is_object($m), 'Expecting "Media" to be object');

		$html = $m->returnHtml();

		$this->assertValidHtml($html);
		$this->outputLine($html);
	}

	public function testMediaVideo () {
		$m = MediaVideo::dimensions(320,480)
			->setFallbackText('test')
			->addMedia('test.mp4')
			->addMedia('test.mpg')
			->setPosterImage('test.jpg')
		;

		$this->assertTrue(is_object($m), 'Expecting "Media" to be object');

		$html = $m->returnHtml();

		$this->assertValidXml($html);
		$this->outputLine($html);
	}

	public function testMediaAudio () {
		$m = MediaAudio::dimensions(320,240)
			->setFallbackText('test')
			->addMedia('test.webma')
			->addMedia('test.mp3')
			->setPosterImage('test.jpg')
		;

		$this->assertTrue(is_object($m), 'Expecting "Media" to be object');

		$html = $m->returnHtml();

		$this->assertValidXml($html);
		$this->outputLine($html);
	}
}

MediaTest::doTest();
