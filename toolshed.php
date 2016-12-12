<?php

use fboes\SmallPhpHelpers\SuperString;

/**
 * Small set of general helpers (aka "The missing PHP functions")
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   MIT License (MIT)
 */

/**
 * Echo with htmlspecialchars
 *
 * @param   string  $string for outputting
 * @param   bool    $withBr convert line breaks into <br />. Optional,
 *  defaults to false
 */
function _echo($string, $withBr = false)
{
    echo(!$withBr
        ? htmlspecialchars($string)
        : nl2br(htmlspecialchars($string))
    );
}

/**
 * Like print_r, but safe for HTML-Output. You may also wnt to try _print_r(var_dump(…));
 *
 * @param   mixed   $mixed
 * @param   string  $mode    as 'pre', 'comment' or 'plain'. Defaults to 'pre'
 */
function _print_r($mixed, $mode = 'pre')
{
    $print_r = htmlspecialchars(print_r($mixed, 1));
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
 * Echos string with some minimal output converting for HTML. Uses _paragraph
 *
 * @param   string  $string
 * @param   bool    $withLinks
 */
function _print_p($string, $withLinks = false)
{
    require_once('SuperString.php');
    SuperString::init($string)->paragraph($withLinks)->e();
}

/**
 * DEPRECATED. See /String class
 * @param      $string
 * @param bool $withLinks
 * @return string
 */
function _paragraph($string, $withLinks = false)
{
    require_once('SuperString.php');
    return SuperString::init($string)->paragraph($withLinks)->r();
}

/**
 * DEPRECATED. See /String class
 * @param      $string
 * @param bool $singleLine
 * @return string
 */
function _textile($string, $singleLine = false)
{
    require_once('SuperString.php');
    return SuperString::init($string)->textile($singleLine)->r();
}

/**
 * DEPRECATED. See /String class
 * @param      $string
 * @param bool $singleLine
 * @return string
 */
function _markdown($string, $singleLine = false)
{
    require_once('SuperString.php');
    return SuperString::init($string)->markdown($singleLine)->r();
}

/**
 * DEPRECATED. See /String class
 * @param $html
 * @return string
 */
function addingTubes($html)
{
    require_once('SuperString.php');
    return SuperString::init($html)->addingTubes(true)->r();
}

/**
 * DEPRECATED. See /String class
 */
function _printf()
{
    $args = func_get_args();
    $format = array_shift($args);
    _vprintf($format, $args);
}

/**
 * DEPRECATED. See /String class
 * @return string
 */
function _sprintf()
{
    $args = func_get_args();
    $format = array_shift($args);
    return _vsprintf($format, $args);
}

/**
 * DEPRECATED. See /String class
 * @param       $format
 * @param array $args
 */
function _vprintf($format, array $args)
{
    echo(_vsprintf($format, $args));
}

/**
 * DEPRECATED. See /String class
 * @param       $format
 * @param array $args
 * @return string
 */
function _vsprintf($format, array $args)
{
    return vsprintf(htmlspecialchars($format), $args);
}

/**
 * DEPRECATED. See /String class
 * @param $string
 * @return string
 */
function htmlallchars($string)
{
    require_once('SuperString.php');
    return SuperString::init($string)->htmlallchars()->r();
}

/**
 * DEPRECATED. See /String class
 * @param        $str
 * @param int    $maxChars
 * @param int    $weight
 * @param string $replace
 * @return string
 */
function str_shorten($str, $maxChars = 72, $weight = 100, $replace = '…')
{
    require_once('SuperString.php');
    return SuperString::init($str)->strShorten($maxChars, $weight, $replace)->r();
}

/**
 * DEPRECATED. See /String class
 * @param        $str
 * @param int    $maxChars
 * @param string $replace
 * @return string
 */
function str_nice_shorten($str, $maxChars = 72, $replace = '…')
{
    require_once('SuperString.php');
    return SuperString::init($str)->str_nice_shorten($maxChars, $replace)->r();
}

/**
 * DEPRECATED. See /String class
 * @param $str
 * @return string
 */
function asciify($str)
{
    require_once('SuperString.php');
    return SuperString::init($str)->asciify()->r();
}

/**
 * DEPRECATED. See /String class
 * @param $str
 * @return string
 */
function make_id($str)
{
    require_once('SuperString.php');
    return SuperString::init($str)->make_id()->r();
}

/**
 * DEPRECATED. See /String class
 * @param      $str
 * @param bool $withHyphenation
 * @param null $langCode
 * @return string
 */
function improve_typography($str, $withHyphenation = true, $langCode = null)
{
    require_once('SuperString.php');
    return SuperString::init($str)->improve_typography($withHyphenation, $langCode)->r();
}

/**
 * Checks if a scalar value is false, without content or only full of whitespaces.
 * For non-scalar values will evaluate if value is empty().
 * @param    mixed    $v    to test
 * @return    bool    if $v is blank
 */
function is_blank(&$v)
{
    return !isset($v) || (is_scalar($v) ? (trim($v) === '') : empty($v));
}

/**
 * Return value of first parameter, or second value if first value evaluates to false by is_blank
 *
 * @see is_blank()
 * @param mixed $v Variable may be empty.
 * @param mixed $defaultValue Fallback value for variable. Optional, defaults to null
 * @return mixed Either $v or $defaultValue, if $v is_blank
 */
function get_value(&$v, $defaultValue = null)
{
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
function make_array($string)
{
    $return = array();
    $lines = preg_split('#[\r\n]+#', trim($string));
    if (!empty($lines)) {
        foreach ($lines as $line) {
            if (preg_match('#^(.+):(.+)$#', trim($line), $matches)) {
                $return[trim($matches[1])] = trim($matches[2]);
            } else {
                $return[] = trim($line);
            }
        }
    }
    return $return;
}

/**
 * Set all internal switches for setting proper locale. Use 'locale -a' to determine available locales on your system.
 * You may also want so send "SET NAMES 'utf8' / 'latin1'" for MySQL.
 *
 * @param   string  $languageCode   according to ISO 639-1
 * @param   string  $countryCode    according to ISO 3166
 * @param   string  $charset    optional, defaults to 'utf-8'
 */
function set_locale($languageCode, $countryCode, $charset = 'UTF-8')
{
    $languageCode = strtolower($languageCode);
    $countryCode  = strtoupper($countryCode);
    $charset      = strtoupper($charset);

    ini_set('default_charset', $charset);
    mb_internal_encoding($charset);

    $localeCode = $languageCode;
    if (!empty($countryCode)) {
        $localeCode .= '_'.$countryCode;
    }
    if (!empty($charset)) {
        $localeCode .= '.'.str_replace(' ', '', $charset);
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
 * @param   string  $countryCode    according to ISO 3166
 * @param   string  $charset    optional, defaults to 'utf-8'
 */
function activate_translations($languageCode, $countryCode, $charset = 'UTF-8')
{
    set_locale($languageCode, $countryCode, $charset);
    bindtextdomain('messages', __DIR__.'/locale');
    textdomain('messages');
}

/**
 * Find best match with HTTP_ACCEPT_LANGUAGE and offered languages
 * @param  array  $availableLangs [description]
 * @return array                  0 => language, 1 => country
 */
function find_best_locale(array $availableLangs = array('en', 'de-de', 'de'))
{
    if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])
        && preg_match('#^('.implode('|', $availableLangs).')#is', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $match)) {
        return explode('-', $match[1]);
    }
    return array();
}

/**
 * Convert filename to a safe filename, stripping all harmful components and doing some minimal pattern checks
 * @param  string $filename       [description]
 * @param  string $directory      [description]
 * @param  string $allowedPattern pattern like '/\.(txt|doc)$/'. Optional
 * @return string                 or null if filename does not match pattern
 */
function safe_filename($filename, $directory, $allowedPattern = null)
{
    $filename  = basename($filename);
    if (!empty($allowedPattern) && !preg_match($allowedPattern, $filename)) {
        return null;
    }
    if (!empty($directory)) {
        if (!preg_match('#/$#', $directory)) {
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
function returnCompleteUrl($urlPart)
{
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
 * @throws \Exception
 */
function importCsv($filename, $useHeader = true, $delimiter = ";")
{
    $data = [];
    $header = array();
    if (!file_exists($filename)) {
        throw new \Exception($filename . ' not found');
    }
    if (($handle = fopen($filename, "r")) !== false) {
        while (($line = fgetcsv($handle, 1000, $delimiter)) !== false) {
            if ($useHeader && empty($header)) {
                foreach ($line as $key => $value) {
                    $line[$key] = preg_replace('#[^a-zA-Z0-9_]#is', '', $value);
                }
                $header = $line;
            } else {
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
    function _($string)
    {
        return $string;
    }
}
