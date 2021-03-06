<?php
namespace fboes\SmallPhpHelpers;

/**
 * @class HttpApi
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   MIT License (MIT)
 */
class HttpApi
{
    protected $baseUrl;
    protected $standardReplyMimeType;
    protected $httpUsername;
    protected $httpPassword;
    protected $cookies = array();

    /**
     * @var Memoization
     */
    protected $memoizationObject;
    protected $memoizationExpire = 5;

    protected $targetEncoding = 'UTF-8';

    protected $allowUnsafe = false;

    /**
     * Properties of last request
     * @var object
     */
    public $lastRequest;

    const REPLY_TYPE_PLAIN   = 'text/plain';
    const REPLY_TYPE_JSON    = 'application/json';
    const REPLY_TYPE_HTML    = 'text/html';
    const REPLY_TYPE_XHTML   = 'application/xhtml+xml';
    const REPLY_TYPE_XML     = 'text/xml';

    const HTTP_METHOD_GET    = 'GET';
    const HTTP_METHOD_POST   = 'POST';
    const HTTP_METHOD_PUT    = 'PUT';
    const HTTP_METHOD_DELETE = 'DELETE';

    /**
     * Invoke HttpApi object
     * @param string $baseUrl   [description]
     * @param string $standardReplyMimeType set to null if you want to use auto-detection of reply MIME type
     */
    public function __construct($baseUrl = null, $standardReplyMimeType = self::REPLY_TYPE_PLAIN)
    {
        $this->baseUrl               = (string)$baseUrl;
        $this->standardReplyMimeType = (string)$standardReplyMimeType;
        $this->clearLastrequest();
    }

    /**
     * Static constructor for chaining
     * @see $this->__construct
     * @param null   $baseUrl
     * @param string $standardReplyMimeType
     * @return \fboes\SmallPhpHelpers\HttpApi
     */
    public static function init($baseUrl = null, $standardReplyMimeType = self::REPLY_TYPE_PLAIN)
    {
        return new self($baseUrl, $standardReplyMimeType);
    }

    /**
     * [clearLastrequest description]
     * @return  HttpApi self
     */
    protected function clearLastrequest()
    {
        $this->lastRequest = (object)array(
            'url'            => null,
            'postFields'     => null,
            'mimeType'       => null,
            'memoizationKey' => null,
            'httpStatusCode' => null,
            'reply'          => null,
        );
        return $this;
    }

    /**
     * Set HTTP authentication credentials for all requests
     * @param string $username [description]
     * @param string $password [description]
     * @return  HttpApi self
     */
    public function setHttpCredentials($username, $password)
    {
        $this->httpUsername = (string)$username;
        $this->httpPassword = (string)$password;
        return $this;
    }

    /**
     * Setcookie to use with all consecutive requests
     * @param string $key   [description]
     * @param string $value [description]
     * @return  HttpApi self
     */
    public function setCookie($key, $value)
    {
        $this->cookies[(string)$key] = (string)$value;
        return $this;
    }

    /**
     * Allow unsafe HTTP requests, e.g. do not check HTTPs authentity
     * @return  HttpApi self
     */
    public function allowUnsafe()
    {
        $this->allowUnsafe = true;
        return $this;
    }

    /**
     * Add Memoization object to be used as query cache.
     * This objects needs to have at least these methods: set($key, $data) and get($key)
     * @param Memoization $memoizationObject [description]
     * @return  HttpApi self
     */
    public function setMemoization(Memoization $memoizationObject)
    {
        if (!is_object($memoizationObject)) {
            error_log('Memoization object is no object');
            exit();
        } elseif (!method_exists($memoizationObject, 'get')
            || !method_exists($memoizationObject, 'set')
        ) {
            error_log('Missing method "get" or "set" in Memoization object');
            exit();
        }
        $this->memoizationObject = $memoizationObject;
        return $this;
    }

    /**
     * Perform a GET-request. Matches "read"
     * @param  array  $query Array of query parameters, with KEY => VALUE
     * @param  string $url   URL for this request. $this->baseUrl will be prepended
     * @return mixed  see $this->doRequest
     */
    public function get(array $query = array(), $url = null)
    {
        return $this->doRequest($query, $url, self::HTTP_METHOD_GET);
    }

    /**
     * Perform a POST-request. Matches "update"
     * @param  array  $query Array of query parameters, with KEY => VALUE
     * @param  string $url   URL for this request. $this->baseUrl will be prepended
     * @return mixed  see $this->doRequest
     */
    public function post(array $query, $url = null)
    {
        return $this->doRequest($query, $url, self::HTTP_METHOD_POST);
    }

    /**
     * Perform a PUT-request. Matches "create"
     * @param  array  $query Array of query parameters, with KEY => VALUE
     * @param  string $url   URL for this request. $this->baseUrl will be prepended
     * @return mixed  see $this->doRequest
     */
    public function put(array $query, $url = null)
    {
        return $this->doRequest($query, $url, self::HTTP_METHOD_PUT);
    }

    /**
     * Perform a DELETE-request. Matches "delete"
     * @param  array  $query Array of query parameters, with KEY => VALUE
     * @param  string $url   URL for this request. $this->baseUrl will be prepended
     * @return mixed  see $this->doRequest
     */
    public function delete(array $query = array(), $url = null)
    {
        return $this->doRequest($query, $url, self::HTTP_METHOD_DELETE);
    }

    /**
     * Do actual request. This will follow redirects, if there are any.
     * @param  array  $query       Array of query parameters, with KEY => VALUE
     * @param  string $url         URL for this request. $this->baseUrl will be prepended
     * @param  string $httpMethod  i.e. 'GET', 'POST', 'PUT', 'DELETE'
     * @return mixed  see $this->convertReply
     * @throws \Exception
     */
    public function doRequest(array $query = null, $url = null, $httpMethod = self::HTTP_METHOD_GET)
    {
        $this->clearLastrequest();
        $url  = $this->baseUrl . (string)$url;
        if (empty($url)) {
            throw new \Exception('Empty URL');
        }
        $httpMethod = (string)$httpMethod;
        $this->lastRequest->memoizationKey = $httpMethod . ' ' .$url;
        if (!empty($query)) {
            $query = http_build_query($query);
            $this->lastRequest->memoizationKey .= '?' . $query;
        }


        if (!empty($this->memoizationObject)) {
            $memoization = $this->memoizationObject->get($this->lastRequest->memoizationKey);
        }
        if (!empty($memoization) && !empty($this->memoizationExpire)) {
            $this->lastRequest->reply = $memoization;
            $this->lastRequest->httpStatusCode = 200;
        } else {
            $ch = curl_init();

            $curlOptions = array();
            $curlOptions[CURLOPT_URL]            = $url;
            $curlOptions[CURLOPT_HEADER]         = 0;
            $curlOptions[CURLOPT_RETURNTRANSFER] = true;
            $curlOptions[CURLOPT_CONNECTTIMEOUT] = 15;
            $curlOptions[CURLOPT_FOLLOWLOCATION] = true;
            $curlOptions[CURLOPT_MAXREDIRS]      = 2;
            if ($this->allowUnsafe) {
                $curlOptions[CURLOPT_SSL_VERIFYPEER] = 0;
                $curlOptions[CURLOPT_SSL_VERIFYHOST] = 0;
            }

            switch ($httpMethod) {
                case self::HTTP_METHOD_POST:
                    $curlOptions[CURLOPT_POST] = true;
                    if (!empty($query)) {
                        $curlOptions[CURLOPT_POSTFIELDS] = $query;
                    }
                    break;
                case self::HTTP_METHOD_PUT:
                    $curlOptions[CURLOPT_POST] = true;
                    $curlOptions[CURLOPT_CUSTOMREQUEST] = $httpMethod;
                    if (!empty($query)) {
                        $curlOptions[CURLOPT_POSTFIELDS] = $query;
                    }
                    break;
                case self::HTTP_METHOD_DELETE:
                    $curlOptions[CURLOPT_POST] = true;
                    $curlOptions[CURLOPT_CUSTOMREQUEST] = $httpMethod;
                    if (!empty($query)) {
                        $curlOptions[CURLOPT_POSTFIELDS] = $query;
                    }
                    break;
                default:
                    if (!empty($query)) {
                        $curlOptions[CURLOPT_URL] .= '?'.$query;
                    }
                    break;
            }
            if (!empty($this->httpUsername) || !empty($this->httpPassword)) {
                $curlOptions[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
                $curlOptions[CURLOPT_USERPWD]  = $this->httpUsername . ':' . $this->httpPassword;
            }
            if (!empty($this->cookies)) {
                $cookies = array();
                foreach ($this->cookies as $key => $value) {
                    $cookies[] = $key.'='.$value;
                }
                $curlOptions[CURLOPT_COOKIE] = implode('; ', $cookies);
            }

            curl_setopt_array($ch, $curlOptions);
            $this->lastRequest->url            = $curlOptions[CURLOPT_URL];
            $this->lastRequest->postFields     = !empty($curlOptions[CURLOPT_POSTFIELDS])
                ? $curlOptions[CURLOPT_POSTFIELDS]
                : null
            ;
            $reply = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new \Exception('Curl Error: '.curl_error($ch));
            } else {
                $this->lastRequest->httpStatusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $this->lastRequest->url            = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
                $this->lastRequest->mimeType       = preg_replace(
                    '#\s?;.+$#',
                    '',
                    curl_getinfo($ch, CURLINFO_CONTENT_TYPE)
                );
                if (!empty($reply)) {
                    $this->lastRequest->reply = $this->convertReply(
                        $reply,
                        empty($this->standardReplyMimeType)
                            ? $this->lastRequest->mimeType
                            : $this->standardReplyMimeType
                    );
                }
                if (!empty($this->memoizationObject) && !empty($this->memoizationExpire)) {
                    $this->memoizationObject->set(
                        $this->lastRequest->memoizationKey,
                        $this->lastRequest->reply,
                        $this->memoizationExpire
                    );
                }
            }
            curl_close($ch);
        }
        return $this->lastRequest->reply;
    }

    /**
     * Check if last HTTP status show the last request to be an error
     * @return boolean [description]
     */
    public function isLastRequestError()
    {
        return $this->lastRequest->httpStatusCode >= 400;
    }

    /**
     * Get last URL from last call. This may be different from the URL you requested because of redirects.
     * @return string URL
     */
    public function getLastUrl()
    {
        return $this->lastRequest->url;
    }

    /**
     * Convert HTTP answer according to selected $replyMimeType to PHP-native representation
     * @param  string $data      [description]
     * @param  string $replyMimeType Convert according to this type. If none is given, if will use the standard
     *                               standardReplyMimeType defined in $this->__construct
     * @return mixed             [description]
     */
    protected function convertReply($data, $replyMimeType = null)
    {
        if (function_exists('mb_detect_encoding')) {
            $foundEncoding = mb_detect_encoding($data);
            if ($foundEncoding != $this->targetEncoding) {
                $data = mb_convert_encoding($data, $this->targetEncoding, $foundEncoding);
            }
        }
        if (empty($replyMimeType)) {
            $replyMimeType = $this->standardReplyMimeType;
        }
        switch ($replyMimeType) {
            case self::REPLY_TYPE_JSON:
                return json_decode($data);
                break;
            case self::REPLY_TYPE_HTML:
                if (class_exists('tidy')) {
                    $tidy = new \tidy(null, array(
                        'clean' => 1,
                    ));
                    $tidy->ParseString($data);
                    $tidy->cleanRepair();
                    $data = $tidy;
                }
                return $data;
                break;
            case self::REPLY_TYPE_XHTML:
                if (class_exists('tidy')) {
                    $tidy = new \tidy(null, array(
                        'clean' => 1,
                        'output-xml' => 1,
                    ));
                    $tidy->ParseString($data);
                    $tidy->cleanRepair();
                    $data = $tidy;
                }
                return simplexml_load_string($data);
                break;
            case self::REPLY_TYPE_XML:
                return simplexml_load_string($data);
                break;
            default:
                return $data;
                break;
        }
    }
}
