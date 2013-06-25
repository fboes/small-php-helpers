<?php

class HttpApi {
	protected $baseUrl;
	protected $replyType;
	protected $httpUsername;
	protected $httpPassword;

	protected $lastUrl;
	protected $lastPostFields;

	public $lastReply;
	public $lastHttpStatusCode;

	const RETURN_TYPE_PLAIN = 'PLAIN';
	const RETURN_TYPE_JSON  = 'JSON';
	const RETURN_TYPE_HTML  = 'HTML';
	const RETURN_TYPE_XHTML = 'XHTML';
	const RETURN_TYPE_XML   = 'XML';

	const HTTP_GET    = 'GET';
	const HTTP_POST   = 'POST';
	const HTTP_PUT    = 'PUT';
	const HTTP_DELETE = 'DELETE';

	/**
	 * Invoke HttpApi object
	 * @param string $baseUrl   [description]
	 * @param string $replyType [description]
	 */
	public function __construct ($baseUrl = NULL, $replyType = self::RETURN_TYPE_PLAIN) {
		$this->baseUrl   = (string)$baseUrl;
		$this->replyType = (string)$replyType;
	}

	/**
	 * Set HTTP authentication credentials for all requests
	 * @param string $username [description]
	 * @param string $password [description]
	 */
	public function setHttpCredentials ($username, $password) {
		$this->httpUsername = (string)$username;
		$this->httpPassword = (string)$password;
	}

	/**
	 * [get description]
	 * @param  array  $data Array of query parameters, with KEY => VALUE
	 * @param  string $url  URL for this request. $this->baseUrl will be prepended
	 * @return mixed  see $this->doRequest
	 */
	public function get (array $data = array(), $url = NULL) {
		return $this->doRequest($data, $url, self::HTTP_GET);
	}

	/**
	 * [post description]
	 * @param  array  $data Array of query parameters, with KEY => VALUE
	 * @param  string $url  URL for this request. $this->baseUrl will be prepended
	 * @return mixed  see $this->doRequest
	 */
	public function post (array $data, $url = NULL) {
		return $this->doRequest($data, $url, self::HTTP_POST);
	}

	/**
	 * [put description]
	 * @param  array  $data Array of query parameters, with KEY => VALUE
	 * @param  string $url  URL for this request. $this->baseUrl will be prepended
	 * @return mixed  see $this->doRequest
	 */
	public function put (array $data, $url = NULL) {
		return $this->doRequest($data, $url, self::HTTP_PUT);
	}

	/**
	 * [delete description]
	 * @param  array  $data Array of query parameters, with KEY => VALUE
	 * @param  string $url  URL for this request. $this->baseUrl will be prepended
	 * @return mixed  see $this->doRequest
	 */
	public function delete (array $data, $url = NULL) {
		return $this->doRequest($data, $url, self::HTTP_DELETE);
	}

	/**
	 * Do actual request
	 * @param  array  $data Array of query parameters, with KEY => VALUE
	 * @param  string $url  URL for this request. $this->baseUrl will be prepended
	 * @param  string $type i.e. 'GET', 'POST'
	 * @return mixed  see $this->convertReply
	 */
	public function doRequest (array $data = NULL, $url = NULL, $type = self::HTTP_GET) {
		$url  = $this->baseUrl . $url;
		$data = http_build_query($data);

		if (empty($url)) {
			throw new Exception('Empty URL');
		}

		$ch = curl_init();

		$curlOptions = array();
		$curlOptions[CURLOPT_URL] = $url;

		switch ($type) {
			case self::HTTP_POST:
				$curlOptions[CURLOPT_POST] = TRUE;
				if (!empty($data)) {
					$curlOptions[CURLOPT_POSTFIELDS] = $data;
				}
				break;
			case self::HTTP_PUT:
				$curlOptions[CURLOPT_POST] = TRUE;
				$curlOptions[CURLOPT_CUSTOMREQUEST] = $type;
				if (!empty($data)) {
					$curlOptions[CURLOPT_POSTFIELDS] = $data;
				}
				break;
			case self::HTTP_DELETE:
				$curlOptions[CURLOPT_POST] = TRUE;
				$curlOptions[CURLOPT_CUSTOMREQUEST] = $type;
				if (!empty($data)) {
					$curlOptions[CURLOPT_POSTFIELDS] = $data;
				}
				break;
			default:
				if (!empty($data)) {
					$curlOptions[CURLOPT_URL] .= '?'.$data;
				}
				break;
		}
		if (!empty($this->httpUsername) || !empty($this->httpPassword)) {
			$curlOptions[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
			$curlOptions[CURLOPT_USERPWD]  = $this->httpUsername . ':' . $this->httpPassword;
		}

		$curlOptions[CURLOPT_HEADER] = 0;
		$curlOptions[CURLOPT_RETURNTRANSFER] = TRUE;
		$curlOptions[CURLOPT_CONNECTTIMEOUT] = 15;

		curl_setopt_array($ch, $curlOptions);
		$this->lastUrl        = $curlOptions[CURLOPT_URL];
		$this->lastPostFields = !empty($curlOptions[CURLOPT_POSTFIELDS])
			? $curlOptions[CURLOPT_POSTFIELDS]
			: NULL
		;
		$reply = curl_exec($ch);

		$this->lastReply = NULL;
		if(curl_errno($ch)) {
			throw new Exception('Curl Error: '.curl_error($ch));
		}
		else {
			$this->lastHttpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE );
			if (!empty($reply)) {
				$this->lastReply = $this->convertReply($reply);
			}
		}
		curl_close($ch);
		return $this->lastReply;
	}

	/**
	 * Check if last HTTP sdtatus show the last request to be an error
	 * @return boolean [description]
	 */
	public function isLastRequestError () {
		return $this->lastHttpStatusCode < 400;
	}

	/**
	 * Convert HTTP answer according to selected $this->replyType to PHP-native represantation
	 * @param  string $data [description]
	 * @return mixed        [description]
	 */
	protected function convertReply ($data) {
		# TODO: output encoding
		switch ($this->replyType) {
			case self::RETURN_TYPE_JSON:
				return json_decode($data);
				break;
			case self::RETURN_TYPE_HTML:
				if (class_exists('tidy')){
					$tidy = new tidy(NULL, array(
						'clean' => 1,
					));
					$tidy->ParseString($data);
					$tidy->cleanRepair();
					$data = $tidy;
				}
				return $data;
				break;
			case self::RETURN_TYPE_XHTML:
				if (class_exists('tidy')){
					$tidy = new tidy(NULL, array(
						'clean' => 1,
						'output-xml' => 1,
					));
					$tidy->ParseString($data);
					$tidy->cleanRepair();
					$data = $tidy;
				}
				return simplexml_load_string($data);
				break;
			case self::RETURN_TYPE_XML:
				return simplexml_load_string($data);
				break;
			default:
				return $data;
				break;
		}
	}
}