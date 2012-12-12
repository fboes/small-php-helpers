<?php
/**
 * @class Media
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   Creative Commons Attribution 3.0 Unported (CC BY 3.0)
 */

class Media {
	protected $width = 0;
	protected $height = 0;
	protected $mediaObjects = array();
	protected $posterImage = '';
	protected $fallbackText = '';

	protected $fileEndingsToMimeType = array(
		'.txt'  => 'text/plain',
		'.xml'  => 'text/xml',
		'.html' => 'text/html',
	);

	public function __construct ($width, $height)  {
		$this->width  = (int)$width;
		$this->height = (int)$height;
	}

	public static function dimensions ($width, $height)  {
		return new static($width, $height);
	}

	/**
	 * Add media to to media object.
	 * @param string $url      [description]
	 * @param string $mimeType [description]
	 * @return self
	 */
	public function addMedia ($url, $mimeType = NULL) {
		if (empty($mimeType)) {
			$mimeType = $this->guessMimeType($url);
		}
		if (!empty($mimeType)) {
			$this->mediaObjects[$mimeType] = $url;
		}
		else {
			throw new Exception('Could not identify MIME type for '.$url.' (or MIME type is not allowed)');
		}
		return $this;
	}

	/**
	 * Add fallback image in case media can not be displayed in current browser, or media has not been activated yet
	 * @param string $url [description]
	 * @return self
	 */
	public function setPosterImage ($url) {
		$this->posterImage = $url;
		return $this;
	}

	/**
	 * Add fallback text in case media can not be displayed in current browser
	 * @param string $text [description]
	 * @return self
	 */
	public function setFallbackText ($text) {
		$this->fallbackText = strip_tags($text);
		return $this;
	}

	public function guessMimeType ($url) {
		if (preg_match('#(\.[a-zA-z0-9]+)$#',$url,$matches)) {
			$fileEnding = strtolower($matches[1]);
			if (!empty($this->fileEndingsToMimeType[$fileEnding])) {
				return $this->fileEndingsToMimeType[$fileEnding];
			}
		}
		return '';
	}

	/**
	 * Return HTML for all current media objects
	 * @return string [description]
	 */
	public function returnHtml () {
		$html = '<div class="media">'."\n";
		$html .= $this->returnHtmlObject($this->mediaObjects);
		$html .= '</div>'."\n";
		return $html;
	}

	protected function returnHtmlObject (array $remainingMediaObjects) {
		$html = '';
		$currentMimeType = current(array_keys($remainingMediaObjects));
		$currentUrl      = array_shift($remainingMediaObjects);
		if (!empty($currentUrl)){
			$html .= '<object data="'.htmlspecialchars($currentUrl).'" type="'.htmlspecialchars($currentMimeType).'" width="'.htmlspecialchars($this->width).'" height="'.htmlspecialchars($this->height).'">'."\n";
			if (!empty($remainingMediaObjects)) {
				$html .= $this->returnHtmlObject($remainingMediaObjects);
			}
			else {
				$html .= $this->retunHtmlPosterImage();
				$html .= $this->retunHtmlFallbackText();
			}
			$html .= '</object>'."\n";
		}
		return $html;
	}

	protected function retunHtmlPosterImage () {
		if (!empty($this->posterImage)) {
			return '<img src="'.htmlspecialchars($this->posterImage).'" alt="" class="poster" />'."\n";
		}
		return '';
	}

	protected function retunHtmlFallbackText () {
		if (!empty($this->fallbackText)) {
			return '<p class="fallback">'.nl2br(htmlspecialchars($this->fallbackText)).'</p>'."\n";
		}
		return '';
	}

	/**
	 * Output HTML as given by $this->returnHtml
	 * @return bool TRUE
	 */
	public function echoHtml () {
		echo $this->returnHtml();
		return TRUE;
	}
}