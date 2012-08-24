<?php
/**
 * @class Messages
 * Collect status messages from your controller for output to the user
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
    $this->messages = array();
    $this->httpStatusCode = 200;
  }

  /**
   * [addSuccessMessage description]
   * @param string  $message [description]
   * @param boolean $success [description]
   */
  public function addSuccessMessage ($message, $success = TRUE) {
    $this->addMessage($message);
    $this->httpStatusCode = max($this->httpStatusCode, $success ? 200 : 400);
    return $this;
  }

  /**
   * [addhttpStatusCode description]
   * @param string  $message    [description]
   * @param integer $httpStatusCode [description]
   */
  public function addHttpStatusCode ($message, $httpStatusCode = 200) {
    $this->addMessage($message);
    $this->httpStatusCode = max($this->httpStatusCode, (int)$httpStatusCode);
    return $this;
  }

  /**
   * Add error message depending on assertion
   * @param bool $assert      [description]
   * @param string $message     [description]
   * @param string $messageFail [description]
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
   * Adds a single message to $this->messages
   * @param string $message [description]
   */
  public function addMessage ($message) {
    $this->messages[] = new Message($message);
  }

  /**
   * [buildhttpStatusCode description]
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

  public function __construct ($message) {
    $this->ts = time();
    $this->message = (string)$message;
  }
}