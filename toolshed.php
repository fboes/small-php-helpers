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
 */
function _print_p ($string)
{
    echo (_paragraph($string));
}

/**
 * Does some minimal output converting for HTML.
 *
 * @param   string  $string
 * @return  string
 */
function _paragraph ($string)
{
    $string = '<p>'.nl2br(htmlspecialchars(trim($string))).'</p>';
    $string = preg_replace('#(<br/?>){2,}#','</p><p>',$string);
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
 * Checks if a scalar value is FALSE, without content or only full 
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
 *
 * @param   string  $languageCode   according to ISO 639-1
 * @param   string  $countryCode    according to ISO 3166
 * @param   string  $charset    optional, defaults to 'utf-8'
 */
function set_locale ($languageCode, $countryCode, $charset = 'utf-8')
{
    $languageCode = strtolower($languageCode);
    $countryCode  = strtoupper($countryCode);
    $charset      = strtolower($charset);
    
    ini_set('default_charset', $charset);

    $localeCode .= '.'.strtoupper(str_replace(' ','',$charset));
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