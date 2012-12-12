<?php
/**
 * @class MediaAudio
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   Creative Commons Attribution 3.0 Unported (CC BY 3.0)
 */

require_once('../Media.class.php');

class MediaAudio extends Media {
	protected $fileEndingsToMimeType = array(
		// HTML5
		'.mp4'  => 'audio/mp4',
		'.m4a'  => 'audio/mp4',
		'.ogg'  => 'audio/ogg',
		'.ogv'  => 'audio/ogg',
		'.webma'=> 'audio/webm',
		// Multi-plattform
		'.mp3'  => 'audio/mpeg',
		// Plattform-specific
		'.wav'  => 'audio/wav',
	);

	public function returnHtml () {
		$method = __FUNCTION__;
		return parent::$method();

		# HTML5 audio tag
		# Flash fallback
		# object tag
		# HTML fallback
	}

	protected function returnHtml5Tag (array $remainingMediaObjects) {
		/*
		<audio controls autobuffer>
		  <source src="thankyou.ogg" />
		  <source src="thankyou.mp3" />
		  <!-- oder doch Flash?! -->
		</audio>
		*/
	}

	protected function returnHtmlFlash (array $remainingMediaObjects) {
	}
}