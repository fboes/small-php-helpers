<?php
# namespace fboes\SmallPhpHelpers;

/**
 * Small set of general helpers (aka "The missing PHP functions")
 *
 * @author	  Frank Bo"es <info@3960.org>
 * @copyright   MIT License (MIT)
 */

/**
 * Echo with htmlspecialchars
 *
 * @param   string  $string for outputting
 * @param   bool	$withBr convert line breaks into <br />. Optional,
 *  defaults to false
 */
function _echo ($string, $withBr = FALSE) {
	echo(!$withBr
		? htmlspecialchars($string)
		: nl2br(htmlspecialchars($string))
	);
}

/**
 * Echos string with some minimal output converting for HTML. Uses _paragraph
 *
 * @param   string  $string
 * @param   bool	$withLinks
 */
function _print_p ($string, $withLinks = FALSE) {
	echo (_paragraph($string, $withLinks));
}

/**
 * Does some minimal output converting for HTML.
 *
 * @param   string  $string
 * @param   bool	$withLinks
 * @return  string
 */
function _paragraph ($string, $withLinks = FALSE) {
	$string = '<p>'.nl2br(htmlspecialchars(trim($string))).'</p>';
	$string = preg_replace('#(<br\s?/?>\s*){2,}#is',"</p>\n<p>",$string);
	if ($withLinks) {
		$string = preg_replace('#(http(s)?\://\S+)#is','<a href="$1">$1</a>',$string);
		$string = preg_replace('#(\S+@\S+)#ise',"'<a href=\"'.htmlallchars('mailto:\\1').'\">'.htmlallchars('\\1').'</a>'",$string);
	}
	return $string;
}

/**
 * Does some mor output converting into HTML using a simple subset of Textile
 * @param  string  $string     [description]
 * @param  boolean $singleLine [description]
 * @return string              [description]
 */
function _textile ($string, $singleLine = FALSE) {
	$str = htmlspecialchars($string);
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
	$str = preg_replace('/(^|\s)\*(\S.*?\S)\*/s','$1<strong>$2</strong>',$str);
	$str = preg_replace('/(^|\s)_(\S.*?\S)_/s','$1<em>$2</em>',$str);
	$str = preg_replace('/(>|\s)@(\S.*?\S)@/s','$1<code>$2</code>',$str);
	$str = preg_replace('/(>|\s)\-(\S.*?\S)\-/s','$1<del>$2</del>',$str);
	$str = preg_replace('/(>|\s)\+(\S.*?\S)\+/s','$1<ins>$2</ins>',$str);
	$str = preg_replace('/(>|\s)\?\?(\S.*?\S)\?\?/s','$1<cite>$2<\/cite>',$str);
	$str = preg_replace('/!(\S+)\(([^\)]+?)\)!/s','<img src="$1" alt="$2" title="$2" \/>',$str);
	$str = preg_replace('/(<img)( src=")(&gt;)/s','$1 style="float:right;margin:0 0 1em 1em;"$2',$str);
	$str = preg_replace('/(<img)( src=")(&lt;)/s','$1 style="float:left;margin:0 1em 1em 0;"$2',$str);
	$str = preg_replace('/<p>(<img[^>]+\/>)<\/p>/s','$1',$str);
	$str = preg_replace('/&quot;(.+?)&quot;\:([^<\s]+[^\.<!\?\s])/s','<a href="$2\">$1</a>',$str);
	$str = preg_replace('/(<a href=\"http.+?\")(>)/s','$1 target="_blank"$2',$str);
	return $str;
}

/**
 * Does some mor output converting into HTML using a simple subset of Markdown
 * @param  string  $string     [description]
 * @param  boolean $singleLine [description]
 * @return string              [description]
 */
function _markdown ($string, $singleLine = FALSE) {
	$str = htmlspecialchars($string);
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
	$str = preg_replace('/(^|\s)\*(\S.*?\S)\*/s','$1<strong>$2</strong>',$str);
	$str = preg_replace('/(^|\s)_(\S.*?\S)_/s','$1<em>$2</em>',$str);
	$str = preg_replace('/(>|\s)`(\S.*?\S)`/s','$1<code>$2</code>',$str);
	$str = preg_replace('/!\[(.*?)\]\((\S+)\)/s','<img src="$2" alt="$1" />',$str);
	$str = preg_replace('/<p>(<img[^>]+\/>)<\/p>/s','$1',$str);
	$str = preg_replace('/\[(.+?)\]\((\S+)\)/s','<a href="$2">$1</a>',$str);
	$str = preg_replace('/(<a href=\"http.+?\")(>)/s','$1 target="_blank"$2',$str);
	return $str;
}

/**
 * Like print_r, but safe for HTML-Output. You may also wnt to try _print_r(var_dump(…));
 *
 * @param   mixed   $mixed
 * @param   mode	as 'pre', 'comment' or 'plain'. Defaults to 'pre'
 */
function _print_r ($mixed, $mode = 'pre') {
	$print_r = htmlspecialchars(print_r($mixed,1));
	switch ($mode) {
		case 'comment':
			echo "<!--\n".$print_r."\n-->";
			break;
		case 'pre':
			echo "<pre>\n".$print_r."\n</pre>";
			break;
		default:
			echo $print_r;
			break;
	}
}


/**
 * Like printf, but $format will be quoted by htmlspecialchars.
 * Arguments will _not_ be quoted.
 *
 * @see  _vprintf()
 * @param   string  $format
 * @param   string  $args   and more args
 */
function _printf () {
	$args = func_get_args();
	$format = array_shift($args);
	_vprintf($format, $args);
}

/**
 * Like sprintf, but $format will be quoted by htmlspecialchars
 * Arguments will _not_ be quoted.
 *
 * @see  _vsprintf()
 * @param   string  $format
 * @param   string  $args   and more args
 * @return  string
 */
function _sprintf () {
	$args = func_get_args();
	$format = array_shift($args);
	return _vsprintf($format, $args);
}

/**
 * Like vprinft, but $format will be quoted by htmlspecialchars
 * Arguments will _not_ be quoted.
 *
 * @see  _vsprintf()
 * @param   string  $format
 * @param   array   $args
 */
function _vprintf ($format, array $args) {
	echo(_vsprintf($format, $args));
}

/**
 * Like vsprintf, but $format will be quoted by htmlspecialchars
 * Arguments will _not_ be quoted.
 *
 * @param   string  $format
 * @param   array   $args
 * @return  string
 */
function _vsprintf ($format, array $args) {
	return vsprintf(htmlspecialchars($format), $args);
}

/**
 * Convert all characters into XML entities.
 *
 * @param   string  $string
 * @return  string
 */
function htmlallchars ($string) {
	$result = NULL;
	$strlen = strlen($string);
	for($i=0 ; $i<$strlen; $i++) {
		$result .= '&#'.ord($string[$i]).';';
	}
	return $result;
}

/**
 * Shorten string to maximum characters as given. Omitted characters will be
 * replaced by a custom character. This function may be too ugly to do
 * editorial shortening of text, because no word boundaries are used. See
 * str_nice_shorten()
 *
 * @param   string  $str
 * @param   int $maxChars   Optional, defaults to 72
 * @param   int $weight Is between 0 and 100, with 100 keeping everything
 *  at the start of the string, 0 keeping everything at the end. Optional,
 *  defaults to 100
 * @param   string  $replace	with what string to replace the omitted
 *  characters. Optional, defaults to a horizontal ellipse in UTF-8
 * @return  string
 */
function str_shorten ($str, $maxChars = 72, $weight = 100, $replace = '…') {
	$strLen = mb_strlen($str);
	if ($strLen > $maxChars) {
		$border = round ($maxChars * max(0,min(100,$weight)) / 100);
		switch ($border) {
			case $maxChars:
				$str = trim(mb_substr($str,0,$maxChars - 1)) . $replace;
				break;
			case 0:
				$str = $replace . trim(mb_substr($str,$strLen - $maxChars +1,$strLen));
				break;
			default:
				$str = mb_ereg_replace('^(.{'.$border.'}).+(.{'.($maxChars - $border -1).'})$', '\1' . $replace . '\2', $str);
				break;
		}
	}
	return $str;
}

/**
 * Shorten string to maximum characters as given. If string is longer than
 * $maxChars, the rest of the string will be replaced by $replace. This
 * function will search for word boundaries, so your string may be even shorter.
 *
 * @param   string  $str
 * @param   int $maxChars   Optional, defaults to 72
 * @param   string  $replace	with what string to replace the omitted
 *  characters. Optional, defaults to a horizontal ellipse in UTF-8
 * @return  string
 */
function str_nice_shorten ($str, $maxChars = 72, $replace = '…') {
	if (mb_strlen($str) > $maxChars) {
		$str = trim(mb_ereg_replace('^(.{0,'.((int)$maxChars-2).'}\W)(.*)$','\1',$str)).$replace;
	}
	return $str;
}

/**
 * Remove any character from string not being a-z, 0-9, _, -, '.'
 *
 * @param   string
 * @return  string
 */
function asciify ($str) {
	if (!preg_match('#^[a-z0-9_\-\.]+$#s', $str)) {
		$str = str_replace(
			array('ä', 'Ä', 'æ', 'ö', 'Ö', 'ü', 'Ü', 'ß'),
			array('ae','AE','ae','oe','OE','ue','UE','ss'),
			$str
		);
		$str = str_replace(array('á','à','â'), 'a', $str);
		$str = str_replace(array('Á','À','Â'), 'A', $str);
		$str = str_replace(array('é','è','ê','ë'), 'e', $str);
		$str = str_replace(array('É','È','Ê'), 'E', $str);
		$str = str_replace(array('ó','ò','ô'), 'o', $str);
		$str = str_replace(array('Ó','Ò','Ô'), 'O', $str);
		$str = str_replace(array('ú','ù','û'), 'u', $str);
		$str = str_replace(array('Ú','Ù','Û'), 'U', $str);
		$str = preg_replace(
			array('#([^a-z0-9_\-\.])#is', '#(_)_+#', '#[^a-z0-9]+$#i', '#^[^a-z0-9]+#i'),
			array('_', '_', '', ''),
			$str
		);
	}
	return $str;
}

/**
 * Convert string to proper IID / Name-Attribute
 * @param  string $str [description]
 * @return string	  [description]
 */
function make_id ($str) {
	if (!preg_match('#^[A-Za-z][A-Za-z0-9\-_]*$#', $str)) {
		$str = preg_replace(
			array('#^[^A-Za-z]#','#[^A-Za-z0-9\-_]#', '#(_)_+#'),
			array('id_$0',       '_',                 '$1'),
			$str
		);
	}
	return $str;
}

/**
 * Convert certain typografical stuff into better typografical stuff
 * See http://de.wikipedia.org/wiki/Anf%C3%BChrungszeichen
 * @param  string  $str            [description]
 * @param  boolean withHyphenation [description]
 * @param  string  $langCode       According to ISO 639-1 2ALPHA, use {{ app.request.locale }}
 * @return string                  [description]
 */
function improve_typography ($str, $withHyphenation = TRUE, $langCode = NULL) {
	if (!is_scalar($str)) {
		throw new \Exception('String expected in improve_typography');
	}
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
	return $str;
}

/**
 * Checks if a scalar value is FALSE, without content or only full of whitespaces. For non-scalar values will evaluate if value is empty().
 *
 * @param	mixed	$v	to test
 * @return	bool	if $v is blank
 */
function is_blank (&$v) {
	return !isset($v) || (is_scalar($v) ? (trim($v) === '') : empty($v));
}

/**
 * Return value of first parameter, or second value if first value evaluates to FALSE by is_blank
 *
 * @see is_blank()
 * @param mixed $v Variable may be empty.
 * @param mixed $defaultValue Fallback value for variable. Optional, defaults to NULL
 * @return Either $v or $defaultValue, if $v is_blank
 */
function get_value (&$v, $defaultValue = NULL) {
	return (!is_blank($v))
		? $v
		: $defaultValue
	;
}

/**
 * Convert string into associative array
 * @param  string $string with x:y
 * @return array with array(x=>y)
 */
function make_array ($string) {
	$return = array();
	$lines = preg_split('#[\r\n]+#', trim($string));
	if (!empty($lines)) {
		foreach ($lines as $line) {
			if (preg_match('#^(.+):(.+)$#',trim($line),$matches)) {
				$return[trim($matches[1])] = trim($matches[2]);
			}
			else {
				$return[] = trim($line);
			}
		}
	}
	return $return;
}

/**
 * Set all internal switches for setting proper locale. Use 'locale -a' to determine available locales on your system. You may also want so send "SET NAMES 'utf8' / 'latin1'" for MySQL.
 *
 * @param   string  $languageCode   according to ISO 639-1
 * @param   string  $countryCode	according to ISO 3166
 * @param   string  $charset	optional, defaults to 'utf-8'
 */
function set_locale ($languageCode, $countryCode, $charset = 'UTF-8') {
	$languageCode = strtolower($languageCode);
	$countryCode  = strtoupper($countryCode);
	$charset	  = strtoupper($charset);

	ini_set('default_charset', $charset);
	mb_internal_encoding($charset);

	$localeCode = $languageCode;
	if (!empty($countryCode)) {
		$localeCode .= '_'.$countryCode;
	}
	if (!empty($charset)) {
		$localeCode .= '.'.str_replace(' ','',$charset);
	}
	$categories = array(LC_COLLATE, LC_CTYPE, LC_TIME);
	if (defined('LC_MESSAGES')) {
		$categories[] = LC_MESSAGES;
	}

	foreach ($categories as $c) {
		setlocale($c, $localeCode);
	}
	$categories = array('LC_COLLATE', 'LC_CTYPE', 'LC_TIME', 'LC_MESSAGES');
	foreach ($categories as $c) {
		putenv($c.'='.$localeCode);
	}
	putenv('LANGUAGE='.$languageCode);
}

/**
 * Like set_locale, but automatically bind translations for this library
 * @param   string  $languageCode   according to ISO 639-1
 * @param   string  $countryCode	according to ISO 3166
 * @param   string  $charset	optional, defaults to 'utf-8'
 */
function activate_translations ($languageCode, $countryCode, $charset = 'UTF-8') {
	set_locale($languageCode, $countryCode, $charset);
	bindtextdomain('messages', __DIR__.'/locale');
	textdomain('messages');
}

/**
 * Find best match with HTTP_ACCEPT_LANGUAGE and offered languages
 * @param  array  $availableLangs [description]
 * @return array                  0 => language, 1 => country
 */
function find_best_locale (array $availableLangs = array('en','de-de','de')) {
	if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) && preg_match('#^('.implode('|',$availableLangs).')#is',$_SERVER['HTTP_ACCEPT_LANGUAGE'],$match)) {
		return explode('-',$match[1]);
	}
	return array();
}

/**
 * Convert filename to a safe filename, stripping all harmful components and doing some minimal pattern checks
 * @param  string $filename       [description]
 * @param  string $directory      [description]
 * @param  string $allowedPattern pattern like '/\.(txt|doc)$/'. Optional
 * @return string                 or NULL if filename does not match pattern
 */
function safe_filename ($filename, $directory, $allowedPattern = NULL) {
	$filename  = basename($filename);
	if (!empty($allowedPattern) && !preg_match($allowedPattern,$filename)) {
		return NULL;
	}
	if (!empty($directory)) {
		if (!preg_match('#/$#',$directory)) {
			$directory .= '/';
		}
		$directory = dirname($directory.'.');
		$directory .= '/';
	}
	return $directory . $filename;
}

/**
 * Return complete URL from partial url
 * @param  string $urlPart [description]
 * @return string          [description]
 */
function returnCompleteUrl ($urlPart) {
	$urlPart = trim($urlPart);
	if (!preg_match('#^[a-z]+://#', $urlPart)) {
		$protocol = (!empty($_SERVER['REQUEST_SCHEME']) && $_SERVER['REQUEST_SCHEME'] == 'https') ? 'https' : 'http';
		$protocol .= '://';
		if (!preg_match('#^www#', $urlPart)) {
			$protocol .= $_SERVER['HTTP_HOST'];
			if (!preg_match('#^/#', $urlPart)) {
				$protocol .= '/';
			}
		}
		$urlPart = $protocol.$urlPart;
	}
	return $urlPart;
}

/**
 * Parse CSV file into array, with first line interpreted as header
 * @param  string  $filename  [description]
 * @param  boolean $useHeader [description]
 * @param  string  $delimiter [description]
 * @return array              of objects
 */
function importCsv ($filename, $useHeader = TRUE, $delimiter = ";") {
	$data = [];
	$header = array();
	if (!file_exists($filename)) {
		throw new Exception($filename . ' not found');
	}
	if (($handle = fopen($filename, "r")) !== FALSE) {
		while (($line = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
			if ($useHeader && empty($header)) {
				foreach ($line as $key => $value) {
					$line[$key] = preg_replace('#[^a-zA-Z0-9_]#is','', $value);
				}
				$header = $line;
			}
			else {
				foreach ($line as $key => $value) {
					$line[$key] = trim($value);
				}
				$data[] = !empty($header)
					? (object)array_combine($header, $line)
					: $line
				;
			}
		}
		fclose($handle);
	}
	return $data;
}


if (!function_exists('_')) {
	function _ ($string) {
		return $string;
	}
}
