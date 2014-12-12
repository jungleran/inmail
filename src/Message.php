<?php
/**
 * @file
 * Contains \Drupal\inmail\Message.
 */

namespace Drupal\inmail;

/**
 *
 *
 * @ingroup processing
 */
class Message {

  /**
   * Extracts email addresses from a To header field.
   *
   * @param string $field
   *   The content of a To header or similar.
   *
   * @return string[]
   *   A list of email addresses.
   */
  public static function parseAddress($field) {
    $parts = preg_split('/\s*,\s*/', trim($field));
    $addresses = [];
    foreach ($parts as $part) {
      if (preg_match('/^\S+@\S+\.\S+$/', $part)) {
        $addresses[] = $part;
      }
      elseif (preg_match('/<(\S+@\S+\.\S+)>$/', $part, $matches)) {
        $addresses[] = $matches[1];
      }
    }
    return $addresses;
  }

}
