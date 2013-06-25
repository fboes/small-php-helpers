<?php
require('../Captcha.interface.php');

/**
 * @class CaptchaSimple
 * Implements simple catcha
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   Creative Commons Attribution 3.0 Unported (CC BY 3.0)
 */
class CaptchaSimple implements InterfaceCaptcha {
	protected $separator = ';';
	protected $salt;
	protected $key;
	protected $sessionLength = 10; // min

	public function __construct ($salt) {
		$this->salt = $salt;
		$this->key = md5($salt + 'key');
	}

	public function getToken () {
		$sessionLength = $this->sessionLength * 60;
		return array(
			md5(floor(time() / $sessionLength) . $this->salt),
			md5(ceil (time() / $sessionLength) . $this->salt)
		);
	}

	public function getHtml () {
		$html = '<input name="'.htmlspecialchars($this->key).'" value="'.htmlspecialchars(implode($this->separator, $this->getToken())).'" />';

		$origSalt = $this->salt;
		$this->salt .= 'wrong';
		$htmlHoneypot = '<input name="'.htmlspecialchars($this->key).'" value="'.htmlspecialchars(implode($this->separator, $this->getToken())).'" />';
		$this->salt = $origSalt;

		return
			'<script>/*<![CDATA[*/document.writeln(\''
			.str_replace(
				array("'",  '>',    'input','name','value'),
				array("\'", "'+'>", "in'+'put","na'+'me","va'+'lue"),
				$html
			)
			.'\');/*]]>*/</script>'."\n"
			.'<!-- '
			.$htmlHoneypot
			.' -->'
		;
	}


	public function echoHtml () {
		echo ($this->getHtml());
	}

	public function isValidToken ($responseToken) {
		$response = explode($this->separator, $responseToken);
		return (bool)array_intersect($this->getToken(), $response);
	}

}