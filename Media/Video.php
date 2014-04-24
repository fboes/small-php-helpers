<?php
# namespace fboes\SmallPhpHelpers\Media;
# use fboes\SmallPhpHelpers\Media;

require_once('../Media.php');

/**
 * @class MediaVideo
 * see http://camendesign.com/code/video_for_everybody
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   MIT License (MIT)
 */
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
		$html = '<div class="media">'."\n";
		$html .= $this->returnHtml5Tag($this->mediaObjects);
		$html .= '</div>'."\n";
		return $html;
	}

	protected function returnHtml5Tag (array $remainingMediaObjects) {
		$html = '%s';

		if (!empty($remainingMediaObjects['video/mp4']) || !empty($remainingMediaObjects['video/ogg']) || !empty($remainingMediaObjects['video/webm'])) {

			$attributes = array(
				'controls="controls"',
				'autobuffer="autobuffer"',
			);
			if (!empty($this->posterImage)) {
				$attributes[] = 'poster="'.htmlspecialchars($this->posterImage).'"';
			}

			$html = '<video ' . implode(' ', $attributes) . $this->returnHtmlDimensionAttribute().'>'."\n";
			foreach (array('video/mp4','video/ogg','video/webm') as $mimeType) {
				if (!empty($remainingMediaObjects[$mimeType])) {
					$html .= "\t".'<source src="'.htmlspecialchars($remainingMediaObjects[$mimeType]).'" type="'.htmlspecialchars($mimeType).'" />'."\n";
				}
			}
			$html .= '%s';
			$html .= '</video>'."\n";
		}

		#$innerHtml = (!empty($remainingMediaObjects))
		#	? $this->returnHtmlFlash($remainingMediaObjects)
		#	: $this->returnHtmlFallback()
		#;
		$innerHtml = $this->returnHtmlObject($remainingMediaObjects);

		return sprintf($html, $innerHtml);
	}

	protected function returnHtmlFlash (array $remainingMediaObjects) {
		$html = '%s';

		$innerHtml = (!empty($remainingMediaObjects))
			? $this->returnHtmlRest($remainingMediaObjects)
			: $this->returnHtmlFallback()
		;

		return sprintf($html, $innerHtml);
	}

	protected function returnHtmlRest (array $remainingMediaObjects) {
		$html = '%s';

		$innerHtml = (!empty($remainingMediaObjects))
			? $this->returnHtmlWindowsMedia($remainingMediaObjects)
			: $this->returnHtmlFallback()
		;

		return sprintf($html, $innerHtml);
	}

	protected function returnHtmlWindowsMedia (array $remainingMediaObjects) {
		$html = '%s';

		$innerHtml = (!empty($remainingMediaObjects))
			? $this->returnHtmlQuicktime($remainingMediaObjects)
			: $this->returnHtmlFallback()
		;

		return sprintf($html, $innerHtml);
	}

	protected function returnHtmlQuicktime (array $remainingMediaObjects) {
		$html = '';

		$innerHtml = $this->returnHtmlFallback();

		return sprintf($html, $innerHtml);
	}

}