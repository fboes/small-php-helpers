<?php
# namespace fboes\SmallPhpHelpers;

/**
 * @class String
 * Chainable string converter
 *
 * new String('Test')->;
 *
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   MIT License (MIT)
 */
class String {
	public $string;

	/**
	 * [__construct description]
	 * @param string $string [description]
	 */
	public function __construct ($string) {
		if (!is_scalar($string)) {
			throw new \Exception('String expected in improve_typography');
		}
		$this->string = $string;
	}

	/**
	 * If object is output in string context, return it's string content
	 * @return string   [description]
	 */
	public function __toString() {
		return $this->string;
	}

	/**
	 * Echo current string
	 * @return self
	 */
	public function e() {
		echo((string)$this);
		return $this;
	}

	/**
	 * Return current string
	 * @return self
	 */
	public function r() {
		return (string)$this;
	}

	/**
	 * Static constructor
	 * @param  string $string [description]
	 * @return self           [description]
	 */
	public static function init ($string) {
		return new self($string);
	}


	/**
	 * Does some minimal output converting for HTML.
	 *
	 * @param   bool	$withLinks
	 * @return  self
	 */
	public function paragraph ($withLinks = FALSE) {
		$this->string = '<p>'.nl2br(htmlspecialchars(trim($this->string))).'</p>';
		$this->string = preg_replace('#(<br\s?/?>\s*){2,}#is',"</p>\n<p>",$this->string);
		if ($withLinks) {
			$this->string = preg_replace('#(http(s)?\://\S+)#is','<a href="$1">$1</a>',$this->string);
			$this->string = preg_replace('#(\S+@\S+)#is','<a href="mailto:$1">$1</a>',$this->string);
		}
		return $this;
	}

	/**
	 * Does some more output converting into HTML using a simple subset of Textile
	 * @param  boolean $singleLine [description]
	 * @return self                [description]
	 */
	public function textile ($singleLine = FALSE) {
		$str = htmlspecialchars($this->string);
		if (!$singleLine) {
			$str = '<p>'.$str.'</p>';
			$str = preg_replace('/\n\n+/s','</p><p>',$str);
			$str = preg_replace('/\n/s','<br />',$str);
			$str = preg_replace('/(<\/p>)(<p>)/s','$1'."\n".'$2',$str);
			$str = preg_replace('/<p>([a-z0-9]+)\(([a-z0-9]+?)\)\.\s(.+?)<\/p>/s','<$1 class="$2">$3</$1>',$str);
			$str = preg_replace('/<p>h(\d)\.\s(.+?)<\/p>/s','<h$1>$2</h$1>',$str);
			$str = preg_replace('/<p>h(\d)\(#([a-zA-Z0-9_-]+)\)\.\s(.+?)<\/p>/s','<h$1 id="$2">$3</h$1>',$str);
			$str = preg_replace('/<p>[*_\-]{3}<\/p>/s','<hr />',$str);
			$str = preg_replace('/(<p>)(&gt;|bq\.)\s(.+?)(<\/p>)/s','<blockquote>$1$3$4</blockquote>',$str);
			$str = preg_replace('/<\/blockquote>(\s*)<blockquote>/s','$1',$str);
			$str = preg_replace('/<p>\*\s(.+?)<\/p>/s','<ul><li>$1</li></ul>',$str);
			$str = preg_replace('/<p>#\s(.+?)<\/p>/s','<ol><li>$1</li></ol>',$str);
			$str = preg_replace('/<br \/>(\*|#)\s/s','<\/li><li>',$str);
			#$str = preg_replace('/<p>\|(.+?)\|<\/p>/s','<table><tr><td>$1</td></tr></table>',$str);
			#$str = preg_replace('/\|<br \/>\|/s','</td></tr>\n<tr><td>',$str);
			#$str = preg_replace('/\|/s','</td><td>',$str);
		}
		else {
			$str = preg_replace('/\n+/s','<br \/>',$str);
		}
		$str = preg_replace('/(^|\s)\*(\S|\S.*?\S)\*/s','$1<strong>$2</strong>',$str);
		$str = preg_replace('/(^|\s)_(\S|\S.*?\S)_/s','$1<em>$2</em>',$str);
		$str = preg_replace('/(>|\s)@(\S|\S.*?\S)@/s','$1<code>$2</code>',$str);
		$str = preg_replace('/(>|\s)\-(\S|\S.*?\S)\-/s','$1<del>$2</del>',$str);
		$str = preg_replace('/(>|\s)\+(\S|\S.*?\S)\+/s','$1<ins>$2</ins>',$str);
		$str = preg_replace('/(>|\s)\?\?(\S|\S.*?\S)\?\?/s','$1<cite>$2<\/cite>',$str);
		$str = preg_replace('/!(\S+)\(([^\)]+?)\)!/s','<img src="$1" alt="$2" title="$2" \/>',$str);
		$str = preg_replace('/(<img)( src=")(&gt;)/s','$1 style="float:right;margin:0 0 1em 1em;"$2',$str);
		$str = preg_replace('/(<img)( src=")(&lt;)/s','$1 style="float:left;margin:0 1em 1em 0;"$2',$str);
		$str = preg_replace('/<p>(<img[^>]+\/>)<\/p>/s','$1',$str);
		$str = preg_replace('/&quot;(.+?)&quot;\:([^<\s]+[^\.<!\?\s])/s','<a href="$2\">$1</a>',$str);
		$this->string = $str;
		return $this;
	}

	/**
	 * Does some more output converting into HTML using a simple subset of Markdown
	 * @param  string  $string     [description]
	 * @param  boolean $singleLine [description]
	 * @return self                [description]
	 */
	public function markdown ($singleLine = FALSE) {
		$str = htmlspecialchars($this->string);
		if (!$singleLine) {
			$str = '<p>'.$str.'</p>';
			$str = preg_replace('/\n\n+/s','</p><p>',$str);
			$str = preg_replace('/\n/s','<br />',$str);
			$str = preg_replace('/(<\/p>)(<p>)/s','$1'."\n".'$2',$str);
			$str = preg_replace('/<p>([^<]+?)<br \/>[=]+<\/p>/s','<h1>$1</h1>',$str);
			$str = preg_replace('/<p>([^<]+?)<br \/>[\-]+<\/p>/s','<h2>$1</h2>',$str);
			$str = preg_replace('/<p>#\s(.+?)<\/p>/s','<h1>$1</h1>',$str);
			$str = preg_replace('/<p>##\s(.+?)<\/p>/s','<h2>$1</h2>',$str);
			$str = preg_replace('/<p>###\s(.+?)<\/p>/s','<h3>$1</h3>',$str);
			$str = preg_replace('/<p>[*_\-]{3}<\/p>/s','<hr />',$str);
			$str = preg_replace('/(<p>)(&gt;|bq\.)\s(.+?)(<\/p>)/s','<blockquote>$1$3$4</blockquote>',$str);
			$str = preg_replace('/(<br \/>)(&gt;|bq\.)\s/s','$1',$str);
			$str = preg_replace('/<\/blockquote>(\s*)<blockquote>/s','$1',$str);
			$str = preg_replace('/<p>\*\s(.+?)<\/p>/s','<ul><li>$1</li></ul>',$str);
			$str = preg_replace('/<p>\d+\.\s(.+?)<\/p>/s','<ol><li>$1</li></ol>',$str);
			$str = preg_replace('/<br \/>(\*|\d+\.)\s/s','</li><li>',$str);
			#$str = preg_replace('/<p>\|(.+?)\|<\/p>/s','<table><tr><td>$1</td></tr></table>',$str);
			#$str = preg_replace('/\|<br \/>\|/s','</td></tr>\n<tr><td>',$str);
			#$str = preg_replace('/\|/s','</td><td>',$str);
		}
		else {
			$str = preg_replace('/\n+/s','<br />',$str);
		}
		$str = preg_replace('/(^|\s)\*(\S|\S.*?\S)\*/s','$1<strong>$2</strong>',$str);
		$str = preg_replace('/(^|\s)_(\S|\S.*?\S)_/s','$1<em>$2</em>',$str);
		$str = preg_replace('/(>|\s)`(\S|\S.*?\S)`/s','$1<code>$2</code>',$str);
		$str = preg_replace('/!\[(.*?)\]\((\S+)\)/s','<img src="$2" alt="$1" />',$str);
		$str = preg_replace('/<p>(<img[^>]+\/>)<\/p>/s','$1',$str);
		$str = preg_replace('/\[(.+?)\]\((\S+)\)/s','<a href="$2">$1</a>',$str);
		$this->string = $str;
		return $this;
	}

	/**
	 * Converting single line links to Youtube or Vimeo into an embedded video player iframe
	 */
	public function addingTubes () {
		$this->string = preg_replace('/(<p>)\s*(?:<a.+>)?[^<]*?youtube.+v=([a-zA-Z0-9\-_]+)[^>]*?(?:<\/a>)?\s*(<\/p>)/s','<div class="video youtube"><iframe src="https://www.youtube.com/embed/$2?enablejsapi=1"></iframe></div>', $this->string);
		$this->string = preg_replace('/(<p>)\s*(?:<a.+>)?[^<]*?vimeo.com\/(\d+)[^>]*?(?:<\/a>)?\s*(<\/p>)/s','<div class="video vimeo"><iframe src="https://player.vimeo.com/video/$2"></iframe></div>', $this->string);
		return $this;
	}

	/**
	 * Like sprintf, but $format will be quoted by htmlspecialchars
	 * Arguments will _not_ be quoted.
	 *
	 * @see  _vsprintf()
	 * @param   string  $format
	 * @param   string  $args   and more args
	 * @return  self
	 */
	public function sprintf () {
		return $this->vsprintf(func_get_args());
	}

	/**
	 * Like vsprintf, but $format will be quoted by htmlspecialchars
	 * Arguments will _not_ be quoted.
	 *
	 * @param   string  $format
	 * @param   array   $args
	 * @return  self
	 */
	public function vsprintf (array $args) {
		$this->string = vsprintf(htmlspecialchars($this->string), $args);
		return $this;
	}

	/**
	 * Convert all characters into XML entities.
	 *
	 * @return  self
	 */
	public function htmlallchars () {
		$result = NULL;
		$strlen = strlen($this->string);
		for($i=0 ; $i<$strlen; $i++) {
			$result .= '&#'.ord($this->string[$i]).';';
		}
		$this->string = $result;
		return $this;
	}

	/**
	 * Shorten string to maximum characters as given. Omitted characters will be
	 * replaced by a custom character. This function may be too ugly to do
	 * editorial shortening of text, because no word boundaries are used. See
	 * str_nice_shorten()
	 *
	 * @param   int $maxChars   Optional, defaults to 72
	 * @param   int $weight Is between 0 and 100, with 100 keeping everything
	 *  at the start of the string, 0 keeping everything at the end. Optional,
	 *  defaults to 100
	 * @param   string  $replace	with what string to replace the omitted
	 *  characters. Optional, defaults to a horizontal ellipse in UTF-8
	 * @return  self
	 */
	public function str_shorten ($maxChars = 72, $weight = 100, $replace = '…') {
		$strLen = mb_strlen($this->string);
		if ($strLen > $maxChars) {
			$border = round ($maxChars * max(0,min(100,$weight)) / 100);
			switch ($border) {
				case $maxChars:
					$this->string = trim(mb_substr($this->string,0,$maxChars - 1)) . $replace;
					break;
				case 0:
					$this->string = $replace . trim(mb_substr($this->string,$strLen - $maxChars +1,$strLen));
					break;
				default:
					$this->string = mb_ereg_replace('^(.{'.$border.'}).+(.{'.($maxChars - $border -1).'})$', '\1' . $replace . '\2', $this->string);
					break;
			}
		}
		return $this;
	}

	/**
	 * Shorten string to maximum characters as given. If string is longer than
	 * $maxChars, the rest of the string will be replaced by $replace. This
	 * function will search for word boundaries, so your string may be even shorter.
	 *
	 * @param   int $maxChars   Optional, defaults to 72
	 * @param   string  $replace	with what string to replace the omitted
	 *  characters. Optional, defaults to a horizontal ellipse in UTF-8
	 * @return  self
	 */
	public function str_nice_shorten ($maxChars = 72, $replace = '…') {
		if (mb_strlen($this->string) > $maxChars) {
			$this->string = trim(mb_ereg_replace('^(.{0,'.((int)$maxChars-2).'}\W)(.*)$','\1',$this->string)).$replace;
		}
		return $this;
	}

	/**
	 * Remove any character from string not being a-z, 0-9, _, -, '.'
	 *
	 * @param   string
	 * @return  self
	 */
	public function asciify () {
		if (!preg_match('#^[a-z0-9_\-\.]+$#s', $this->string)) {
			$this->string = str_replace(
				array('ä', 'Ä', 'æ', 'ö', 'Ö', 'ü', 'Ü', 'ß'),
				array('ae','AE','ae','oe','OE','ue','UE','ss'),
				$this->string
			);
			$this->string = str_replace(array('á','à','â'), 'a', $this->string);
			$this->string = str_replace(array('Á','À','Â'), 'A', $this->string);
			$this->string = str_replace(array('é','è','ê','ë'), 'e', $this->string);
			$this->string = str_replace(array('É','È','Ê'), 'E', $this->string);
			$this->string = str_replace(array('ó','ò','ô'), 'o', $this->string);
			$this->string = str_replace(array('Ó','Ò','Ô'), 'O', $this->string);
			$this->string = str_replace(array('ú','ù','û'), 'u', $this->string);
			$this->string = str_replace(array('Ú','Ù','Û'), 'U', $this->string);
			$this->string = preg_replace(
				array('#([^a-z0-9_\-\.])#is', '#(_)_+#', '#[^a-z0-9]+$#i', '#^[^a-z0-9]+#i'),
				array('_', '_', '', ''),
				$this->string
			);
		}
		return $this;
	}

	/**
	 * Convert string to proper IID / Name-Attribute
	 * @param  string $str [description]
	 * @return self  	  [description]
	 */
	public function make_id () {
		if (!preg_match('#^[A-Za-z][A-Za-z0-9\-_]*$#', $this->string)) {
			$this->string = preg_replace(
				array('#^[^A-Za-z]#','#[^A-Za-z0-9\-_]#', '#(_)_+#'),
				array('id_$0',       '_',                 '$1'),
				$this->string
			);
		}
		return $this;
	}

	/**
	 * Convert certain typografical stuff into better typografical stuff
	 * See http://de.wikipedia.org/wiki/Anf%C3%BChrungszeichen
	 * @param  boolean withHyphenation [description]
	 * @param  string  $langCode       According to ISO 639-1 2ALPHA, use {{ app.request.locale }}
	 * @return self                    [description]
	 */
	public function improve_typography ($withHyphenation = TRUE, $langCode = NULL) {
		$str = $this->string;
		if (empty($langCode)) {
			$langCode = getenv('LANGUAGE');
		}
		$str = trim($str);
		$str = str_replace('--', '—', $str);
		$str = str_replace('...', '…', $str);
		$str = str_replace('… …', '… ', $str);
		$str = str_replace('(C)', '©', $str);
		$str = str_replace('(R)', '®', $str);
		$str = str_replace('(TM)', '™', $str);
		$str = str_replace('(+-)', '±', $str);
		$str = str_replace('(1/4)', '¼', $str);
		$str = str_replace('(1/2)', '½', $str);
		$str = str_replace('(3/4)', '¾', $str);
		$str = str_replace('->', '→', $str);
		$str = str_replace('=>', '⇒', $str);
		$str = str_replace('<-', '←', $str);
		$str = str_replace('<=', '⇐', $str);
		$str = preg_replace('#(\d)\s*-\s*(\d)#is','$1–$2',$str);
		$str = preg_replace('#(\s)-(\s)#is','$1–$2',$str);
		$str = preg_replace('#(\d\s*)(x|\*)(\s*\d)#is','$1×$3',$str);

		if (!empty($langCode)) {
			switch ($langCode) {
				case 'af': # Afrikaans
				case 'bg': # Bulgarian
				case 'cs': # Czech
				case 'de': # German
				case 'et': # Estonian
				case 'fi': # Finnish
				case 'hr': # Croatian
				case 'hu': # Hungarian
				case 'is': # Icelandic
				case 'ka': # Georgian
				case 'lt': # Lithuanian
				case 'lv': # Latvian
				case 'pl': # Polish
				case 'ro': # Romanian
				case 'sk': # Slovak
				case 'sl': # Slovenian
				case 'sr': # Serbian
					$str = preg_replace('#"(\S.*\S)"#is','„$1“',$str);
					$str = preg_replace("#'(\S.*\S)'#is",'‚$1‘',$str);
					break;
				case 'ar': # Arabic
				case 'be': # Belarusian
				case 'ca': # Catalan
				case 'el': # Modern Greek (1453-)
				case 'es': # Spanish
				case 'eu': # Basque
				case 'fr': # French
				case 'hy': # Armenian
				case 'it': # Italian
				case 'no': # Norwegian
				case 'pt': # Portuguese
				case 'ru': # Russian
				case 'sq': # Albanian
				case 'uk': # Ukrainian
					$str = preg_replace('#"(\S.*\S)"#is','«$1»',$str);
					$str = preg_replace("#'(\S.*\S)'#is",'‹$1›',$str);
					break;
				case 'da': # Danish
					$str = preg_replace('#"(\S.*\S)"#is','»$1«',$str);
					$str = preg_replace("#'(\S.*\S)'#is",'›$1‹',$str);
					break;
				default:
					$str = preg_replace('#"(\S.*\S)"#is','“$1”',$str);
					$str = preg_replace("#'(\S.*\S)'#is",'‘$1’',$str);
					break;
			}
			if ($withHyphenation) {
				switch ($langCode) {
					case 'de':
						$str = mb_ereg_replace('(\w)(lich|dorf|stadt|burg|berg|markt|straße|baden|tig|den)(\W)', '\1­\2\3', $str);
						$str = mb_ereg_replace('(\W)(ver|vor|zer|ab|aus|auf)(\w)', '\1\2­\3', $str);
						break;
					case 'en':
						$str = mb_ereg_replace('(\w)(town|ing|tion|ly)(\W)', '\1­\2\3', $str);
						$str = mb_ereg_replace('(\W)(per)(\w)', '\1\2­\3', $str);
						break;
				}
			}
		}
		$this->string = $str;
		return $this;
	}

	/**
	 * Add target="blank" to all external links
	 * @return [type] [description]
	 */
	public function externalLinks () {
		$this->string = preg_replace('/(<a href=\"http.+?\")(>)/s','$1 target="_blank"$2',$this->string);
		return $this;
	}
}
