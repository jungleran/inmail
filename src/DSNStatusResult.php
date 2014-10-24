<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\DSNStatusResult.
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
class DSNStatusResult implements AnalyzerResultInterface {

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
   * The intended recipient address of the message that bounced.
   *
   * @var string
   */
  protected $recipient;

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
      '0' => 'Other address status',
      '1' => 'Bad destination mailbox address',
      '2' => 'Bad destination system address',
      '3' => 'Bad destination mailbox address syntax',
      '4' => 'Destination mailbox address ambiguous',
      '5' => 'Destination address valid',
      '6' => 'Destination mailbox has moved, No forwarding address',
      '7' => 'Bad sender\'s mailbox address syntax',
      '8' => 'Bad sender\'s system address',
    ),
    '2' => array(
      '0' => 'Other or undefined mailbox status',
      '1' => 'Mailbox disabled, not accepting messages',
      '2' => 'Mailbox full',
      '3' => 'Message length exceeds administrative limit',
      '4' => 'Mailing list expansion problem',
    ),
    '3' => array(
      '0' => 'Other or undefined mail system status',
      '1' => 'Mail system full',
      '2' => 'System not accepting network messages',
      '3' => 'System not capable of selected features',
      '4' => 'Message too big for system',
      '5' => 'System incorrectly configured',
    ),
    '4' => array(
      '0' => 'Other or undefined network or routing status',
      '1' => 'No answer from host',
      '2' => 'Bad connection',
      '3' => 'Directory server failure',
      '4' => 'Unable to route',
      '5' => 'Mail system congestion',
      '6' => 'Routing loop detected',
      '7' => 'Delivery time expired',
    ),
    '5' => array(
      '0' => 'Other or undefined protocol status',
      '1' => 'Invalid command',
      '2' => 'Syntax error',
      '3' => 'Too many recipients',
      '4' => 'Invalid command arguments',
      '5' => 'Wrong protocol version',
    ),
    '6' => array(
      '0' => 'Other or undefined media error',
      '1' => 'Media not supported',
      '2' => 'Conversion required and prohibited',
      '3' => 'Conversion required but not supported',
      '4' => 'Conversion with loss performed',
      '5' => 'Conversion Failed',
    ),
    '7' => array(
      '0' => 'Other or undefined security status',
      '1' => 'Delivery not authorized, message refused',
      '2' => 'Mailing list expansion prohibited',
      '3' => 'Security conversion required but not possible',
      '4' => 'Security features not supported',
      '5' => 'Cryptographic failure',
      '6' => 'Cryptographic algorithm not supported',
      '7' => 'Message integrity failure',
    ),
  );

  /**
   * Constructs a DSNStatusResult object.
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
   *   A new DSNStatusResult object for the given code.
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

  public function isSuccess() {
    return $this->class == 2;
  }

  public function isTransientFailure() {
    return $this->class == 4;
  }

  public function isPermanentFailure() {
    return $this->class == 5;
  }

  public function isFailure() {
    return !$this->isSuccess();
  }

  public function setRecipient($address) {
    $this->recipient = trim($address) ?: NULL;
  }

  public function getRecipient() {
    return $this->recipient;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->getClassLabel() . ': ' . $this->getDetailLabel();
  }
}
