<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\DSNType.
 */

namespace Drupal\bounce_processing;

/**
 * A message type corresponding to the RFC 3463 specification.
 *
 * RFC 3463 "Enhanced Mail System Status Codes" defines numerical codes for
 * Delivery Status Notifications (DSN).
 *
 * @see https://tools.ietf.org/html/rfc3463
 * @see http://tools.ietf.org/html/rfc3464
 */
class DSNType implements MessageTypeInterface {

  /**
   * First part of status code.
   *
   * @var int
   */
  protected $class;

  /**
   * Second part of status code.
   *
   * @var int
   */
  protected $subject;

  /**
   * Third part of status code.
   *
   * @var int
   */
  protected $detail;

  /**
   * Labels for the class part, as specified by the RFC.
   *
   * @var array
   */
  private $classMap = array(
    '2' => 'Success',
    '4' => 'Persistent Transient Failure',
    '5' => 'Permanent Failure',
  );

  /**
   * Labels for the subject and detail parts, as specified by the RFC.
   *
   * @var array
   */
  private $detailMap = array(
    '0' => array(
      '0' => 'Other undefined status',
    ),
    '1' => array(
      '1' => 'Bad destination mailbox address',
    ),
    '2' => array(
      '2' => 'Mailbox full',
    ),
    '7' => array(
      '1' => 'Delivery not authorized, message refused',
    ),
    // @todo Cover all detail codes.
  );

  /**
   * Constructs a DSNType object.
   *
   * @param int|string $class
   *   The first number in the status code.
   * @param int|string $subject
   *   The second number in the status code.
   * @param int|string $detail
   *   The third number in the status code.
   *
   * @throws \InvalidArgumentException
   *   If the given code parts are not in accordance with the RFC.
   */
  public function __construct($class, $subject, $detail) {
    if (!in_array($class, [2, 4, 5])) {
      throw new \InvalidArgumentException("Invalid 'class' part: $class.");
    }
    if (!in_array($subject, range(0, 7))) {
      throw new \InvalidArgumentException("Invalid 'subject' part: $subject");
    }
    if (!isset($this->detailMap[$subject][$detail])) {
      throw new \InvalidArgumentException("Invalid 'detail' part: $subject.$detail");
    }
    $this->class = intval($class);
    $this->subject = intval($subject);
    $this->detail = intval($detail);
  }

  /**
   * Parses a three-number status code.
   *
   * @param string $code
   *   Three-number status code.
   *
   * @return static
   *   A new DSNType object for the given code.
   *
   * @throws \InvalidArgumentException
   *   If the given code is not in accordance with the RFC.
   */
  public static function parse($code) {
    $parts = explode('.', $code);
    if (count($parts) == 3) {
      return new static($parts[0], $parts[1], $parts[2]);
    }
    // Unreachable point. Above statement either returns or throws.
  }

  public function getCode() {
    return "$this->class.$this->subject.$this->detail";
  }

  public function getClassLabel() {
    return $this->classMap[$this->class];
  }

  public function getDetailLabel() {
    return $this->detailMap[$this->subject][$this->detail];
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->getClassLabel() . ': ' . $this->getDetailLabel();
  }
}
