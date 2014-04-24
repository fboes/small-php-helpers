<?php
# namespace fboes\SmallPhpHelpers\Media;
# use fboes\SmallPhpHelpers\Media;

require_once('../Media.php');

/**
 * @class MediaAudio
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   MIT License (MIT)
 */
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
		$html = '<div class="media">'."\n";
		$html .= $this->returnHtml5Tag($this->mediaObjects);
		$html .= '</div>'."\n";
		return $html;
	}

	protected function returnHtml5Tag (array $remainingMediaObjects) {
		$html = '%s';

		if (!empty($remainingMediaObjects['audio/mp4']) || !empty($remainingMediaObjects['audio/ogg']) || !empty($remainingMediaObjects['audio/webm']) || !empty($remainingMediaObjects['audio/mpeg'])) {

			$attributes = array(
				'controls="controls"',
				'autobuffer="autobuffer"',
			);

			$html = '<audio ' . implode(' ', $attributes) . $this->returnHtmlDimensionAttribute().'>'."\n";
			foreach (array('audio/mp4','audio/ogg','audio/webm','audio/mpeg') as $mimeType) {
				if (!empty($remainingMediaObjects[$mimeType])) {
					$html .= "\t".'<source src="'.htmlspecialchars($remainingMediaObjects[$mimeType]).'" type="'.htmlspecialchars($mimeType).'" />'."\n";
				}
			}
			$html .= '%s';
			$html .= '</audio>'."\n";
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
			? $this->returnHtmlObject($remainingMediaObjects)
			: $this->returnHtmlFallback()
		;

		return sprintf($html, $innerHtml);
	}
}