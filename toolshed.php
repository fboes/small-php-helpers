<?php
/**
 * Small set of general helpers (aka "The missing PHP functions")
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   Creative Commons Attribution 3.0 Unported (CC BY 3.0)
 */

/**
 * Echo with htmlspecialchars
 *
 * @param   string  $string for outputting
 * @param   bool    $withBr convert line breaks into <br />. Optional,
 *  defaults to false
 */
function _echo ($string, $withBr = FALSE)
{
    echo(!$withBr
        ? htmlspecialchars($string)
        : nl2br(htmlspecialchars($string))
    );
}

/**
 * Echos string with some minimal output converting for HTML. Uses _paragraph
 *
 * @param   string  $string
 * @param   bool    $withLinks
 */
function _print_p ($string, $withLinks = FALSE)
{
    echo (_paragraph($string, $withLinks));
}

/**
 * Does some minimal output converting for HTML.
 *
 * @param   string  $string
 * @param   bool    $withLinks
 * @return  string
 */
function _paragraph ($string, $withLinks = FALSE)
{
    $string = '<p>'.nl2br(htmlspecialchars(trim($string))).'</p>';
    $string = preg_replace('#(<br\s?/?>\s*){2,}#is',"</p>\n<p>",$string);
    if ($withLinks)
    {
        $string = preg_replace('#(http(s)?\://\S+)#is','<a href="$1">$1</a>',$string);
        $string = preg_replace('#(\S+@\S+)#ise',"'<a href=\"'.htmlallchars('mailto:\\1').'\">'.htmlallchars('\\1').'</a>'",$string);
    }
    return $string;
}

/**
 * Like print_r, but safe for HTML-Output.
 *
 * @param   mixed   $mixed
 * @param   mode    as 'pre', 'comment' or 'plain'. Defaults to 'pre'
 */
function _print_r ($mixed, $mode = 'pre')
{
    $print_r = htmlspecialchars(print_r($mixed,1));
    switch ($mode)
    {
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
 * @param   string  $format
 * @param   string  $args   and more args
 */
function _printf ()
{
    $args = func_get_args();
    $format = array_shift($args);
    _vprintf($format, $args);
}

/**
 * Like sprintf, but $format will be quoted by htmlspecialchars
 * Arguments will _not_ be quoted.
 *
 * @param   string  $format
 * @param   string  $args   and more args
 * @return  string
 */
function _sprintf ()
{
    $args = func_get_args();
    $format = array_shift($args);
    return _vsprintf($format, $args);
}

/**
 * Like vprinft, but $format will be quoted by htmlspecialchars
 * Arguments will _not_ be quoted.
 *
 * @param   string  $format
 * @param   array   $args
 */
function _vprintf ($format, array $args)
{
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
function _vsprintf ($format, array $args)
{
    return vsprintf(htmlspecialchars($format), $args);
}

/**
 * Convert all characters into XML entities.
 *
 * @param   string  $string
 * @return  string
 */
function htmlallchars ($string)
{
    $result = NULL;
    for($i=0 ; $i<strlen($string) ; $i++)
    {
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
 * @param   string  $replace    with what string to replace the omitted
 *  characters. Optional, defaults to a horizontal ellipse in UTF-8
 * @return  string
 */
function str_shorten ($str, $maxChars = 72, $weight = 100, $replace = '…')
{
    $strLen = mb_strlen($str);
    if ($strLen > $maxChars)
    {
        $border = round ($maxChars * max(0,min(100,$weight)) / 100);
        switch ($border)
        {
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
 * @param   string  $replace    with what string to replace the omitted
 *  characters. Optional, defaults to a horizontal ellipse in UTF-8
 * @return  string
 */
function str_nice_shorten ($str, $maxChars = 72, $replace = '…')
{
    if (mb_strlen($str) > $maxChars)
    {
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
function asciify ($str)
{
    if (!preg_match('#^[a-z0-9_\-\.]+$#s', $str))
    {
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
 * @return string      [description]
 */
function make_id ($str)
{
    if (!preg_match('#^[A-Za-z][A-Za-z0-9\-_\:\.]+$#', $str))
    {
        $str = preg_replace(
            array('#^[A-Za-z]#','#[A-Za-z0-9\-_\:\.]#', '#(_)_+#'),
            array('id',         '_',                    ''),
            $str
        );
    }
    return $str;
}

/**
 * Checks if a scalar value is FALSE, without content or only full of
 * whitespaces.
 * For non-scalar values will evaluate if value is empty().
 *
 * @param	mixed	$v	to test
 * @return	bool	if $v is blank
 */
function is_blank (&$v)
{
    return !isset($v) || (is_scalar($v) ? (trim($v) === '') : empty($v));
}

/**
 * Set all internal switches for setting proper locale. Use 'locale -a'
 * to determine available locales on your system.
 * You may also want so send "SET NAMES 'utf8' / 'latin1'" for MySQL.
 *
 * @param   string  $languageCode   according to ISO 639-1
 * @param   string  $countryCode    according to ISO 3166
 * @param   string  $charset    optional, defaults to 'utf-8'
 */
function set_locale ($languageCode, $countryCode, $charset = 'UTF-8')
{
    $languageCode = strtolower($languageCode);
    $countryCode  = strtoupper($countryCode);
    $charset      = strtoupper($charset);

    ini_set('default_charset', $charset);
    mb_internal_encoding($charset);

    $localeCode .= '.'.str_replace(' ','',$charset);
    $categories = array(LC_COLLATE, LC_CTYPE, LC_TIME, LC_MESSAGES);
    foreach ($categories as $c)
    {
        setlocale($c, $localeCode);
    }
}

if (!function_exists('_'))
{
    function _ ($string)
    {
        return $string;
    }
}
?>