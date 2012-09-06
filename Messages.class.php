<?php
/**
 * @class Messages
 * Collect status messages from your controller for output to the user.
 * Uses HTTP status codes for showing status of message.
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   Creative Commons Attribution 3.0 Unported (CC BY 3.0)
 */
class Messages {
  public $httpStatusCode = 200;
  public $messages = array();

  /**
   * [__construct description]
   */
  public function __construct () {
    $this->httpStatusCode = 200;
    $this->messages = array();
  }

  /**
   * Adds a single message to $this->messages
   * @param string $message [description]
   * @param  int $httpStatusCode  200 for ok, 400 for client error, 500 for server error. You may use any other HTTP status code
   * @return  Messages [description]
   */
  public function addMessage ($message, $httpStatusCode = 200) {
    $this->messages[] = new Message($message, (int)$httpStatusCode);
    $this->httpStatusCode = max($this->httpStatusCode, (int)$httpStatusCode);
    return $this;
  }

  /**
   * Add message and tell if it is a success message. Uses $this->addMessage
   * @param string  $message [description]
   * @param boolean $success [description]
   * @return  Messages [description]
   */
  public function addSuccessMessage ($message, $success = TRUE) {
    $httpStatusCode = $success ? 200 : 400;
    $this->addHttpStatusCode($message, $httpStatusCode);
    return $this;
  }

  /**
   * Add message or failure message, depending on assertion. Uses $this->addSuccessMessage
   * @param bool $assert      [description]
   * @param string $message     [description]
   * @param string $messageFail [description]
   * @return  Messages [description]
   */
  public function addMessageOnAssert ($assert, $message, $messageFail = '') {
    if (empty($messageFail)) {
      $messageFail = str_replace(
        array(' success',' succeeded', ' exists', ' is', ' has'),
        array(' fail',' failed', ' does not exist', ' is not', ' has not'),
        $message
      );
    }
    $this->addSuccessMessage($assert ? $message : $messageFail, $assert);
    return $this;
  }

  /**
   * Return status code and last message to be used with header()
   * @return string [description]
   */
  public function buildhttpStatusCode () {
    $message = !empty($this->messages)
      ? end($this->messages)->message
      : ''
    ;
    return 'HTTP/1.1 ' . $this->httpStatusCode . ' ' . $message;
  }

  /**
   * [isSuccess description]
   * @return boolean [description]
   */
  public function isSuccess () {
    return $this->httpStatusCode < 400;
  }
}

/**
 * @class Message
 * A single message
 */
class Message {
  public $ts;
  public $message;
  public $httpStatusCode;

  /**
   * [__construct description]
   * @param string  $message [description]
   * @param integer $httpStatusCode  200 for OK, 400 for client error, 500 for server error. You may also add any other HTTP status code
   */
  public function __construct ($message, $httpStatusCode = 200) {
    $this->ts = time();
    $this->message = (string)$message;
    $this->httpStatusCode = (int)$httpStatusCode;
  }
}