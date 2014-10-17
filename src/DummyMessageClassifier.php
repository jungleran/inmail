<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\DummyMessageClassifier.
 */

namespace Drupal\bounce_processing;

/**
 * Extremely simple MessageClassifierInterface instance.
 */
class DummyMessageClassifier implements MessageClassifierInterface {

  /**
   * {@inheritdoc}
   */
  public function classify(Message $message) {
    if (strpos($message->getBody(), 'bounce') !== FALSE) {
      return static::TYPE_BOUNCE;
    }
    return static::TYPE_REGULAR;
  }
}
