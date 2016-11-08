<?php

namespace Drupal\inmail\MIME;
use Drupal\Component\Datetime\DateTimePlus;

/**
 * Provides common helper methods for MultiPartMessage.
 */
trait MimeMessageTrait {

  /**
   * Returns the header of the entity.
   *
   * @see \Drupal\inmail\MIME\MimeEntityInterface
   *
   * @return \Drupal\inmail\MIME\MimeHeader
   *   The header.
   */
  abstract public function getHeader();

  /**
   * Converts body from Puny-code to UTF8.
   *
   * @param string $body
   *   Field body to be decoded.
   *
   * @return string|null
   *   Decoded UTF8 address if successful decoding, otherwise NULL.
   */
  protected function decodeAddress($body) {
    // Skip decoding if the intl package is missing.
    if (!function_exists('idn_to_utf8')) {
      return $body;
    }

    if (strpos($body, '@') !== FALSE) {
      // Extracting body after '@' sign for proper IDN decoding.
      $body = explode('@', $body, 2)[0] . '@' . \idn_to_utf8(explode('@', $body, 2)[1]);
    }
    return $body;
  }

  /**
   * {@inheritdoc}
   */
  public function getFrom($decode = FALSE) {
    $mailboxes = $this->parseDecodeField('From', $decode);
    return isset($mailboxes[0]) ? $mailboxes[0] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getTo($decode = FALSE) {
    return $this->parseDecodeField('To', $decode);
  }

  /**
   * {@inheritdoc}
   */
  public function getCc($decode = FALSE) {
    return $this->parseDecodeField('Cc', $decode);
  }

  /**
   * {@inheritdoc}
   */
  public function getReplyTo($decode = FALSE) {
    return $this->parseDecodeField('Reply-To', $decode);
  }

  /**
   * Parses address field and decodes on request.
   *
   * @param $name
   *   The field name.
   * @param $decode
   *
   * @return \array[]|null
   */
  protected function parseDecodeField($name, $decode = FALSE) {
    $body = $this->getHeader()->getFieldBody($name);
    $mailboxes = MimeParser::parseAddress($body);
    if ($decode) {
      foreach ($mailboxes as $key => $mailbox) {
        $mailboxes[$key]['address'] = $this->decodeAddress($mailboxes[$key]['address']);
      }
    }
    return $body ? $mailboxes : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getReceivedDate() {
    // A message can have one or more Received header fields. The first
    // occurring is the latest added. Its body has two parts separated by ';',
    // the second part being a date.
    if (!$received_body = $this->getHeader()->getFieldBody('Received')) {
      return NULL;
    }
    list($info, $date_string) = explode(';', $received_body, 2);
    return $this->parseTimezone($date_string);
  }

  /**
   * {@inheritdoc}
   */
  public function getDate() {
    $date_string = $this->getHeader()->getFieldBody('Date');
    return $this->parseTimezone($date_string);
  }

  /**
   * Returns cleaned and parsed date-time object.
   *
   * @param string $date_string.
   *   The date string.
   *
   * @return \Drupal\Component\DateTime\DateTimePlus
   *   The date object without time zone abbreviation.
   */
  protected function parseTimezone($date_string) {
    // By RFC2822 time-zone abbreviation is invalid and needs to be removed.
    // Match only capital letters within the brackets at the end of string.
    $date_string = preg_replace('/\(([A-Z]+)\)$/', '', $date_string);
    return new DateTimePlus($date_string);
  }

}
