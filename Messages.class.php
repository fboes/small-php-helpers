<?php
/**
 * @class Messages
 * Collect status messages from your controller for output to the user.
 * Uses HTTP status codes for showing status of message, see
 * http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
 * Status codes of note:
 * - 200  OK
 * - 201  Created
 * - 301  Moved permanently
 * - 303  Created, redirecting to new ressource
 * - 307  Temporary redirect
 * - 400  Client error: Bad request
 * - 404  Client error: Not found
 * - 409  Client error: Conflict
 * - 410  Client error: Gone
 * - 500  Internal server error
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   Creative Commons Attribution 3.0 Unported (CC BY 3.0)
 */
class Messages {
  public $httpStatusCode = 200;
  public $messages = array();
  const SESSION_OBJECT = 'PHP.Messages.Object';

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
   * @param  int $httpStatusCode  200 for ok, 400 for client error, 500 for server error.
   *                              You may use any other HTTP status code
   * @return  Messages [description]
   */
  public function addMessage ($message, $httpStatusCode = 200) {
    $this->messages[] = new Message($message, (int)$httpStatusCode);
    $this->httpStatusCode = max($this->httpStatusCode, (int)$httpStatusCode);
    return $this;
  }

  /**
   * Add message and tell if it is a success message.
   * Uses $this->addMessage
   * @param string  $message [description]
   * @param boolean $success [description]
   * @return  Messages [description]
   */
  public function addSuccessMessage ($message, $success = TRUE) {
    $httpStatusCode = $success ? 200 : 400;
    $this->addMessage($message, $httpStatusCode);
    return $this;
  }

  /**
   * Add message or failure message, depending on assertion.
   * Uses $this->addSuccessMessage
   * @param bool $assert      [description]
   * @param string $message     [description]
   * @param string $messageFail [description]
   * @return  Messages [description]
   */
  public function addMessageOnAssert ($assert, $message, $messageFail = '') {
    if (empty($messageFail)) {
      $messageFail = str_replace(
        array(' success',' succeeded', 'successful',      'exist',        'exists',         ' is',     ' has',     'have',     'was'),
        array(' fail',   ' failed',    'not successful',  'do not exist', 'does not exist', ' is not', ' has not', 'have not', 'was not'),
        $message
      );
    }
    $this->addSuccessMessage($assert ? $message : $messageFail, $assert);
    return $this;
  }

  /**
   * Return HTTP 1.1 status code and last message to be used with header()
   * @return string [description]
   */
  public function buildhttpStatusCode () {
    $message = !empty($this->messages)
      ? ' ' . end($this->messages)->message
      : ''
    ;
    return 'HTTP/1.1 ' . $this->httpStatusCode . $message;
  }

  /**
   * [isSuccess description]
   * @return boolean [description]
   */
  public function isSuccess () {
    return $this->httpStatusCode < 400;
  }

  /**
   * Return all messages as log format
   * @param  string $delimiter Characters to separate lines by
   * @return string            [description]
   */
  public function returnLogLines ($delimiter = "\n") {
    $output = '';
    foreach ($this->messages as $message) {
      $output .= $message->returnLogLine() . $delimiter;
    }
    return $output;
  }

  /**
   * Write logfile
   * @param  string $filename [description]
   * @return bool           [description]
   */
  public function writeLogFile ($filename) {
    return (file_put_contents($filename, $this->returnLogLines(), FILE_APPEND | LOCK_EX) !== FALSE);
  }

  /**
   * Store current status of messages in session for output after reload.
   * Assumes $_SESSION to be present
   * @return  Messages [description]
   */
  public function storeInSession () {
    if (!empty($_SESSION)) {
      $_SESSION[self::SESSION_OBJECT] = serialize($this);
    }
    else {
      throw new Exception('Missing initialized session.');
    }
    return $this;
  }

  /**
   * Restore status of messages from session for output after reload
   * @return  Messages [description]
   */
  public function restoreFromSession () {
    if (!empty($_SESSION[self::SESSION_OBJECT])) {
      $that = serialize($_SESSION[self::SESSION_OBJECT]);
      $thatVars = get_object_vars($that);
      foreach ($thatVars as $varName => $varValue) {
        $this->$varName = $varValue;
      }
    }
    return $this;
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
   * @param integer $httpStatusCode  200 for OK, 400 for client error, 500 for server error.
   *                                 You may also add any other HTTP status code
   */
  public function __construct ($message, $httpStatusCode = 200) {
    $this->ts = time();
    $this->message = (string)$message;
    $this->httpStatusCode = (int)$httpStatusCode;
  }

  /**
   * [isSuccess description]
   * @return boolean [description]
   */
  public function isSuccess () {
    return $this->httpStatusCode < 400;
  }

  /**
   * Return current message as log event
   * @return  string [description]
   */
  public function returnLogLine () {
    return '['.date('r',$this->ts).'] ['.($this->isSuccess() ? 'ok' : 'error').'] '.$this->httpStatusCode.' '.$this->message;
  }
}