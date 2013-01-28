<?php
/**
 * @class Integration
 *
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   Creative Commons Attribution 3.0 Unported (CC BY 3.0)
 */

class Integration {

	static public function videoIframe ($url) {
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

}