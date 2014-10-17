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

  // @todo These are stupid.
  const TYPE_BOUNCE = 'bounce';
  const TYPE_REGULAR = 'regular';

  /**
   * Analyzes a message and returns its type.
   *
   * @param Message $message
   *   An incoming message.
   *
   * @return string
   *   The type that the message is judged to belong to. Possible values are the
   *   TYPE_* constants of this interface.
   */
  public function classify(Message $message);

}
