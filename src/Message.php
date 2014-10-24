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
   * The raw message string.
   *
   * @var string
   */
  protected $raw;

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
   * The enumerated parts in case of a multipart message.
   *
   * @var string[]
   */
  protected $parts;

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
      $parts = explode(':', $header, 2);
      if (strcasecmp($parts[0], $header_name) == 0) {
        return trim($parts[1]);
      }
    }
    return NULL;
  }

  /**
   * Returns whether this message is a multipart message.
   *
   * Multipart messages combine multiple parts, each of its own MIME type.
   *
   * @return bool
   *   TRUE if this is a multipart message, FALSE otherwise.
   */
  public function isMultipart() {
    return (bool) preg_match('@^multipart/@i', $this->getHeader('Content-Type'));
  }

  /**
   * Returns whether this is a Delivery Status Notification (DSN) message.
   *
   * @return bool
   *   TRUE if this is a DSN message, FALSE otherwise.
   */
  public function isDSN() {
    return (bool) preg_match('@^multipart/report\s*;@i', $this->getHeader('Content-Type'));
  }

  /**
   * Returns the body of the mail message.
   *
   * @return string
   *   The message body.
   */
  public function getBody() {
    return $this->body;
  }

  /**
   * Returns the parts of multipart message.
   *
   * @return string[]|null
   *   If this is a multipart message, a list of parts is returned, each
   *   containing a body and contingent headers. Otherwise NULL is returned.
   */
  public function getParts() {
    return $this->parts;
  }

  /**
   * Returns the raw message string.
   *
   * @return string
   *   The raw message string.
   */
  public function getRaw() {
    return $this->raw;
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
    $message->raw = $raw;

    // Normalize to using only \n for newlines.
    $raw = str_replace("\r\n", "\n", $raw);

    // A blank line separates headers from the body.
    list($headers, $message->body) = explode("\n\n", $raw, 2);

    // Join so-called folded (multi-line) headers.
    $headers = preg_replace('/\n([\s])/', '\1', $headers);
    $message->headers = explode("\n", $headers);

    // Identify and split a multipart message.
    if ($message->isMultipart()) {
      $boundarychar = '0-9a-zA-Z\'()+_,\-./:=?';
      if (preg_match("@boundary=\"([$boundarychar ]+[$boundarychar])\"@", $message->getHeader('Content-Type'), $matches)) {
        $boundary = $matches[1];
        $message->parts = explode("\n--$boundary\n", $message->getBody());
      }
    }

    return $message;
  }

}
