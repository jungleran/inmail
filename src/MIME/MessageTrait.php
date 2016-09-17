<?php

namespace Drupal\inmail\MIME;

/**
 * Provides common helper methods for MultiPartMessage.
 */
trait MessageTrait {

  /**
   * {@inheritdoc}
   */
  public function getTo($decode = FALSE) {
    $body = $this->getHeader()->getFieldBody('To');
    if ($decode) {
      if (strpos($body, '@') !== FALSE) {
        // Extracting body after '@' sign for proper IDN decoding.
        $body = explode('@', $body, 2)[0] . '@' . idn_to_utf8(explode('@', $body, 2)[1]);
      }
      //@todo Properly parse Mail Address https://www.drupal.org/node/2800585
    }
    return $body;
  }

  /**
   * {@inheritdoc}
   */
  public function getFrom($decode = FALSE) {
    $body = $this->getHeader()->getFieldBody('From');
    if ($decode) {
      if (strpos($body, '@') !== FALSE) {
        $body = explode('@', $body, 2)[0] . '@' . idn_to_utf8(explode('@', $body, 2)[1]);
      }
      //@todo Properly parse Mail Address https://www.drupal.org/node/2800585
    }
    return $body;
  }

  /**
   * Returns the header of the entity.
   *
   * @see \Drupal\inmail\MIME\EntityInterface
   *
   * @return \Drupal\inmail\MIME\Header
   *   The header.
   */
  abstract public function getHeader();

}
