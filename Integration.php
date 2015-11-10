<?php
# namespace fboes\SmallPhpHelpers;

/**
 * @class Integration
 * Integrate content from other plattforms, like video plyers etc
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   MIT License (MIT)
 */

class Integration {

	/**
	 * Return an iframe URL for a video player by looking at the given URL. This method may be handy for to have URLs copied from the URL bar in Youtube or Vimeo to convert it to the real player URL.
	 * @param  string $url Some URL from a video plattform
	 * @return string      URL to use as src-attribute for an iFrame
	 */
	static public function videoIframe ($url) {
		$returnUrl = NULL;
		if (preg_match('#(youtu\.be|youtube|vimeo)#',strtolower($url),$portal)) {
			switch ($portal[1]) {
				case 'youtu.be':
					if (preg_match('#youtu\.be/([^&]+)#is', $url, $id)) {
						$returnUrl = 'https://www.youtube.com/embed/' . $id[1] . '?enablejsapi=1';
					}
				case 'youtube':
					if (preg_match('#youtube.+v=([^&]+)#is',$url,$id)) {
						$returnUrl = 'https://www.youtube.com/embed/' . $id[1]  . '?enablejsapi=1';
					}
					break;
				case 'vimeo':
					if (preg_match('#vimeo.com/([0-9]+)#is',$url,$id)) {
						$returnUrl = 'https://player.vimeo.com/video/' . $id[1] . '?api=1';
					}
					break;
			}
		}
		return $returnUrl;
	}

	/**
	 * Generate list of social links
	 * @param  string $url         [description]
	 * @param  string $title       [description]
	 * @param  string $description [description]
	 * @param  string $image       [description]
	 * @return array               with SERVICE => URL
	 */
	static function socialLinks ($url, $title, $description = NULL, $image = NULL) {
		return array(
			'Facebook'    => 'https://www.facebook.com/sharer.php?u='.rawurlencode($url),
			'Twitter'     => 'https://twitter.com/intent/tweet?original_referer='.rawurlencode($url).'&source=tweetbutton&text='.rawurlencode($title.' '.$description).'&url='.rawurlencode($url),
			'Pinterest'   => !empty($image) ? 'http://pinterest.com/pin/create/button/?url='.rawurlencode($url).'&media='.rawurlencode($image).'&description='.rawurlencode($description) : NULL,
			'Google Plus' => 'https://plus.google.com/share?url='.rawurlencode($url),
			'Email'       => 'mailto:?subject='.rawurlencode($title).'&body='.rawurlencode($description.' ['.$url.']'),
			'LinkedIn'    => 'https://www.linkedin.com/shareArticle?mini=true&url='.rawurlencode($url).'&title='.rawurlencode($title).'&summary='.rawurlencode($description).'&source=',
			'print'       => 'javascript:window.print();',
		);
	}
}
