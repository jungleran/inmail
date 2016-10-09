<?php

namespace Drupal\inmail\MIME;

/**
 * Provides common helper methods for MultiPartMessage.
 */
trait MessageTrait {

  /**
   * Returns the header of the entity.
   *
   * @see \Drupal\inmail\MIME\EntityInterface
   *
   * @return \Drupal\inmail\MIME\Header
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
  protected function getDecodedAddress($body) {
    //@todo: Properly parse Mail Address https://www.drupal.org/node/2800585.

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
    //@todo: Properly parse Mail Address https://www.drupal.org/node/2800585.

    $body = $this->getHeader()->getFieldBody('From');
    if ($decode) {
      $body = $this->getDecodedAddress($body);
    }
    return $body;
  }

  /**
   * {@inheritdoc}
   */
  public function getTo($decode = FALSE) {
    //@todo Properly parse Mail Address https://www.drupal.org/node/2800585
    //@todo Deal with multiple recipients.

    $body = $this->getHeader()->getFieldBody('To');
    if ($decode) {
      $body = $this->getDecodedAddress($body);
    }
    return $body ? [$body] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCc($decode = FALSE) {
    //@todo Properly parse Mail Address https://www.drupal.org/node/2800585
    //@todo Deal with multiple recipients.

    $body = $this->getHeader()->getFieldBody('Cc');
    if ($decode) {
      $body = $this->getDecodedAddress($body);
    }
    return $body ? [$body] : NULL;
  }

}
