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
    $body = $this->getHeader()->getFieldBody('From');
    $mailboxes = MimeParser::parseAddress($body);
    $mailbox = reset($mailboxes);
    if ($decode) {
      $mailbox['address'] = $this->decodeAddress($mailbox['address']);
    }
    return $mailbox;
  }

  /**
   * {@inheritdoc}
   */
  public function getTo($decode = FALSE) {
    $body = $this->getHeader()->getFieldBody('To');
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
  public function getCc($decode = FALSE) {
    $body = $this->getHeader()->getFieldBody('Cc');
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
    // A message has one or more Received header fields. The first occurring is
    // the latest added. Its body has two parts separated by ';', the second
    // part being a date.
    $received_body = $this->getHeader()->getFieldBody('Received');
    list($info, $date_string) = explode(';', $received_body, 2);
    // By RFC2822 time-zone abbreviation is invalid and needs to be removed.
    // Match only capital letters within the brackets at the end of string.
    $date_string = preg_replace('/\(([A-Z]+)\)$/', '', $date_string);
    return new DateTimePlus($date_string);
  }

}
