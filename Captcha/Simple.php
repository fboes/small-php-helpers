<?php

namespace fboes\SmallPhpHelpers\Captcha;

use fboes\SmallPhpHelpers\InterfaceCaptcha;

/**
 * @class CaptchaSimple
 * Implements simple catcha
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   MIT License (MIT)
 */
class Simple implements InterfaceCaptcha
{
    protected $separator = ';';
    protected $salt;
    protected $key;
    protected $sessionLength = 10; // min

    /**
     * CaptchaSimple constructor.
     * @param $salt
     */
    public function __construct($salt)
    {
        $this->salt = $salt;
        $this->key = md5($salt . 'key');
    }

    /**
     * @return array
     */
    public function getToken()
    {
        $sessionLength = $this->sessionLength * 60;
        return array(
            md5(floor(time() / $sessionLength) . $this->salt),
            md5(ceil(time() / $sessionLength)  . $this->salt)
        );
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        $html = '<input name="'.htmlspecialchars($this->key).'" value="'
            .htmlspecialchars(implode($this->separator, $this->getToken())).'" />';

        $origSalt = $this->salt;
        $this->salt .= 'wrong';
        $htmlHoneypot = '<input name="'.htmlspecialchars($this->key).'" value="'
            .htmlspecialchars(implode($this->separator, $this->getToken())).'" />';
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

    /**
     * Echo current HTML
     */
    public function echoHtml()
    {
        echo ($this->getHtml());
    }

    /**
     * @param $responseToken
     * @return bool
     */
    public function isValidToken($responseToken)
    {
        $response = explode($this->separator, $responseToken);
        return (bool)array_intersect($this->getToken(), $response);
    }
}
