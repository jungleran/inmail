<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageClassifierInterface.
 */

namespace Drupal\bounce_processing;

/**
 * Provides methods to determine the type of a message.
 */
interface MessageClassifierInterface {

  /**
   * Analyzes a message and returns its type.
   *
   * @param Message $message
   *   An incoming message.
   *
   * @return string
   *   An RFC 3463 mail system status code identified by the classification. If
   *   the classification fails, it should return "2.0.0" which is the generic
   *   success code.
   *
   * @see http://tools.ietf.org/html/rfc1891
   * @see http://tools.ietf.org/html/rfc3463
   */
  public function classify(Message $message);

}
