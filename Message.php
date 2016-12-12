<?php
namespace fboes\SmallPhpHelpers;

/**
 * @class Message
 * A single message
 */
class Message
{
    public $ts;
    public $message;
    public $httpStatusCode;

    /**
     * [__construct description]
     * @param string  $message [description]
     * @param integer $httpStatusCode  200 for OK, 400 for client error, 500 for server error.
     *                                 You may also add any other HTTP status code
     */
    public function __construct($message, $httpStatusCode = 200)
    {
        $this->ts = time();
        $this->message = (string)$message;
        $this->httpStatusCode = (int)$httpStatusCode;
    }

    /**
     * [isSuccess description]
     * @return boolean [description]
     */
    public function isSuccess()
    {
        return $this->httpStatusCode < 400;
    }

    /**
     * Return current message as log event
     * @return  string [description]
     */
    public function returnLogLine()
    {
        return '['.date('r', $this->ts).'] ['.($this->isSuccess() ? 'ok' : 'error').'] '
        .$this->httpStatusCode.' '.$this->message;
    }
}
