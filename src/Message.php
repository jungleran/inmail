<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\Message.
 */

namespace Drupal\bounce_processing;

/**
 * A mail message, minimally parsed into a body text and a list of mail headers.
 */
class Message {

  /**
   * The mail message headers.
   *
   * @var string[]
   */
  protected $headers;

  /**
   * The mail message body text.
   *
   * @var string
   */
  protected $body;

  /**
   * Returns the headers of the mail message.
   *
   * @return string[]
   *   An indexed array with values of the form "Header: Content".
   */
  public function getHeaders() {
    return $this->headers;
  }

  /**
   * Returns the content of a given header from the mail message.
   *
   * @param string $header_name
   *   Name of the header.
   *
   * @return string|null
   *   The content of the header, or NULL if header is not present.
   */
  public function getHeader($header_name) {
    foreach ($this->headers as $header) {
      $parts = explode(': ', $header, 2);
      if ($parts[0] == $header_name) {
        return $parts[1];
      }
    }
    return NULL;
  }

  /**
   * Returns the body of the mail message.
   *
   * @todo What is multipart?
   *
   * @return string
   *   The message body.
   */
  public function getBody() {
    return $this->body;
  }

  /**
   * Parses a raw message into a typed Message.
   *
   * @param string $raw
   *   Raw mail message.
   *
   * @return \Drupal\bounce_processing\Message
   *   Parsed message.
   */
  public static function parse($raw) {
    $message = new Message();
    list($headers, $message->body) = explode("\n\n", $raw, 2);
    $message->headers = explode("\n", $headers);
    return $message;
  }

}
