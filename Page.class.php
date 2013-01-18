<?php
/**
 * @class Page
 * Page Media functionality
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   Creative Commons Attribution 3.0 Unported (CC BY 3.0)
 */

class Page {
	protected $title;
	protected $description;
	protected $url;
	protected $sitename;

	/**
	 * Set basic properties of your site
	 * @param string $title       [description]
	 * @param string $url         [description]
	 * @param string $description Optional
	 * @param string $sitename    Optional, fallback will be domain name
	 */
	public function __construct ($title, $url, $description = NULL, $sitename = NULL) {
		$this->title = $title;
		$this->description = $description;

		if (!$this->isAbsoluteUrl($url)) {
			throw new Exception('No protocol identifier found in URL, please use absolute URL');
		}

		$this->url   = $url;
		$this->sitename = !empty($sitename) ? $sitename : $this->getDomain();
	}

	/**
	 * [basicMeta description]
	 * @param  string $titlePattern %1$s being the site name, %2$s being the page title
	 * @return string HTML
	 */
	public function basicMeta ($titlePattern = '%2$s - %1$s') {
		return
			'<title>'.htmlspecialchars(sprintf($titlePattern, $this->sitename, $this->title)).'</title>'."\n"
			.'<meta name="description" content="'.htmlspecialchars($this->description).'" />'."\n"
		;
	}

	/**
	 * Return HTML for Opengraph Meta tags
	 * @param  string $imageUrl [description]
	 * @param  string $type [description]
	 * @return string HTML
	 */
	public function opengraphMeta ($imageUrl = NULL, $type = 'article') {
		if (!empty($imageUrl) && !$this->isAbsoluteUrl($imageUrl)) {
			throw new Exception('No protocol identifier found in image URL, please use absolute URL');
		}
		return
			'<meta property="og:site_name" content="'.htmlspecialchars($this->sitename).'" />'."\n"
			.'<meta property="og:title" content="'.htmlspecialchars($this->title).'" />'."\n"
			.'<meta property="og:description" content="'.htmlspecialchars($this->description).'" />'."\n"
			.'<meta property="og:url" content="'.htmlspecialchars($this->url).'" />'."\n"
			.'<meta property="og:type" content="'.htmlspecialchars($type).'" />'."\n"
			.(!empty($imageUrl) ? '<meta property="og:image" content="'.htmlspecialchars($imageUrl).'" />'."\n" : '')
		;
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
	 * Return URL to share this page via mail.
	 * @param  string $subject Pattern: use %1$s for title, %2$s for URL, %3$s for sitename, %4$s for description
	 * @param  string $body    Pattern: use %1$s for title, %2$s for URL, %3$s for sitename, %4$s for description
	 * @return string [description]
	 */
	public function emailUrl ($subject = 'Recommendation from %3$s', $body = 'Recommending "%1$s" (%2$s) from %3$s.') {
		$subject = sprintf(_($subject), $this->title, $this->url, $this->sitename, $this->description);
		$body    = sprintf(_($body),    $this->title, $this->url, $this->sitename, $this->description);

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
	public function popupEvent () {
		return "window.open(this.href, 'social', 'width:640,height:320,resizable:yes,toolbar:no');return false;";
	}

	/*
	function videoIframe ($url) {
	  $returnUrl = NULL;
	  if (preg_match('#(youtu\.be|youtube|vimeo)#',strtolower($url),$portal)) {
	    switch ($portal[1]) {
	      case 'youtu.be':
	        if (preg_match('#youtu\.be/([^&]+)#is', $url, $id)) {
	          $returnUrl = 'https://www.youtube.com/embed/' . $id[1] ;
	        }
	      case 'youtube':
	        if (preg_match('#youtube.+v=([^&]+)#is',$url,$id)) {
	          $returnUrl = 'https://www.youtube.com/embed/' . $id[1] ;
	        }
	        break;
	      case 'vimeo':
	        if (preg_match('#vimeo.com/([0-9]+)#is',$url,$id)) {
	          $returnUrl = 'https://player.vimeo.com/video/' . $id[1] ;
	        }
	        break;
	    }
	  }
	  return $returnUrl;
	}
	*/

	protected function getDomain () {
		return preg_replace('#^[a-zA-z]+://([^/]+).*$#','$1',$this->url);
	}

	protected function isAbsoluteUrl ($url) {
		return preg_match('#^[a-zA-Z]+://#',$url);
	}
}