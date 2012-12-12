<?php
/**
 * @class MediaVideo
 * see http://camendesign.com/code/video_for_everybody
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   Creative Commons Attribution 3.0 Unported (CC BY 3.0)
 */

require_once('../Media.class.php');

class MediaVideo extends Media {
	protected $fileEndingsToMimeType = array(
		// HTML5
		'.mp4'  => 'video/mp4',
		'.m4v'  => 'video/mp4',
		'.ogg'  => 'video/ogg',
		'.ogv'  => 'video/ogg',
		'.webm' => 'video/webm',
		// Multi-plattform
		'.mpeg' => 'video/mpeg',
		'.mpg'  => 'video/mpeg',
		'.flv'  => 'video/x-flv',
		// Plattform-specific
		'.mov'  => 'video/quicktime',
		'.qt'   => 'video/quicktime',
		'.wmv'  => 'video/x-ms-wmv',
		'.avi'  => 'video/x-msvideo',
	);


	public function returnHtml () {
		$method = __FUNCTION__;
		return parent::$method();

		# HTML5 video tag
		# Flash fallback
		# object tag
		# Windows Media tag
		# Quicktime tag
		# HTML fallback
	}

	protected function returnHtml5Tag (array $remainingMediaObjects) {
		/*
		<video width="640" height="360" controls>
			<!-- MP4 must be first for iPad! -->
			<source src="__VIDEO__.MP4" type="video/mp4" /><!-- Safari / iOS video    -->
			<source src="__VIDEO__.OGV" type="video/ogg" /><!-- Firefox / Opera / Chrome10 -->
		</video>
		*/
	}

	protected function returnHtmlFlash (array $remainingMediaObjects) {
	}
}