<?php
/**
 * @class Social
 * Social Media functionality
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   Creative Commons Attribution 3.0 Unported (CC BY 3.0)
 */

class Social {
	protected $url;
	protected $title;

	public function __construct ($title, $url) {
		$this->title = $title;

		if (!preg_match('#^[a-zA-Z]+://#',$url)) {
			throw new Exception('No protocol identifier found in URL');
		}

		$this->url   = $url;
	}

	/**
	 * Return Facebook Share URL for this page
	 * @return string [description]
	 */
	public function facebookUrl () {
		return 'http://www.facebook.com/sharer.php?u='.rawurlencode($this->url);
	}

	/**
	 * Return Twitter Tweet URL for this page
	 * @param  string $text  Optional, if not set title of this page will be used as tweet text
	 * @return string [description]
	 */
	public function twitterUrl ($text = '') {
		$text = (!empty($text)) ? $text : $this->title;
		return 'https://twitter.com/intent/tweet?original_referer='.rawurlencode($this->url).'&source=tweetbutton&text'.rawurlencode($text).'&url='.rawurlencode($this->url).'';
	}

	/**
	 * Return URL to share this page via mail
	 * @param  string $subject [description]
	 * @param  string $body    [description]
	 * @return string [description]
	 */
	public function emailUrl ($subject = 'Recommendation from %1$s', $body = 'Recommending "%1$s" (%2$s) from %3$s.') {
		$subject = sprintf(_($subject), $this->title, $this->url,$this->getDomain());
		$body    = sprintf(_($body),    $this->title, $this->url, $this->getDomain());

		return 'mailto:?subject='.rawurlencode($subject).'&body='.rawurlencode($body);
	}

	/**
	 * Return URL to print this page
	 * @return string [description]
	 */
	public function printUrl () {
		return 'javascript:window.print();';
	}

	/**
	 * Optional onclick-attribute value for link to open in nice extra window
	 * @return [type] [description]
	 */
	public function onclickEvent () {
		return "window.open(this.href, 'social', 'width:640,height:320,resizable:yes,toolbar:no');return false;";
	}

	protected function getDomain () {
		return preg_replace('#^[a-zA-z]://(.+?)/.*$#','$1',$this->url);
	}

}