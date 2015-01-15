<?php
/**
 * @file
 * Contains \Drupal\inmail\MIME\Entity.
 */

namespace Drupal\inmail\MIME;

/**
 * A MIME entity is typically an email message or a part of a multipart message.
 *
 * @ingroup mime
 */
class Entity implements EntityInterface {

  /**
   * The entity header.
   *
   * @var \Drupal\inmail\MIME\Header
   */
  protected $header;

  /**
   * The entity body.
   *
   * @var string
   */
  protected $body;

  /**
   * Constructs a new Entity.
   *
   * @param \Drupal\inmail\MIME\Header $header
   *   The entity header.
   * @param string $body
   *   The entity body.
   */
  public function __construct(Header $header, $body) {
    $this->header = $header;
    $this->body = $body;
  }

  /**
   * {@inheritdoc}
   */
  public function getHeader() {
    return $this->header;
  }

  /**
   * {@inheritdoc}
   */
  public function getContentType() {
    $field = $this->getHeader()->getFieldBody('Content-Type');
    if (empty($field)) {
      // Default content type defined in RFC 2045 sec 5.2.
      $field = 'text/plain; charset=us-ascii';
    }
    $field_parts = preg_split('/\s*;\s*/', $field);

    list($type, $subtype) = explode('/', array_shift($field_parts));

    $parameters = array();
    foreach ($field_parts as $part) {
      list($attribute, $value) = preg_split('/\s*=\s*/', $part, 2);
      // Trim surrounding quotes.
      $parameters[strtolower($attribute)] = trim($value, '"');
    }

    return array(
      'type' => $type,
      'subtype' => $subtype,
      'parameters' => $parameters,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getContentTransferEncoding() {
    $field = $this->getHeader()->getFieldBody('Content-Transfer-Encoding');
    if (empty($field)) {
      // Default encoding defined in RFC 2045 sec 6.1.
      $field = '7bit';
    }
    return $field;
  }

  /**
   * {@inheritdoc}
   */
  public function getMessageId() {
    return $this->getHeader()->getFieldBody('Message-Id');
  }

  /**
   * {@inheritdoc}
   */
  public function getBody() {
    return $this->body;
  }

  /**
   * {@inheritdoc}
   */
  public function toString() {
    // A blank line terminates the header section and begins the body.
    return $this->getHeader()->toString() . "\n\n" . $this->getBody();
  }

}
