<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageAnalyzer\BounceClassifier.
 */

namespace Drupal\bounce_processing\MessageAnalyzer;

use Drupal\bounce_processing\Message;

/**
 * Provides methods to determine the type of a message.
 */
abstract class BounceClassifier implements MessageAnalyzerInterface {

  /**
   * Classifies a message and returns its type.
   *
   * @param \Drupal\bounce_processing\Message $message
   *   An incoming message.
   *
   * @return \Drupal\bounce_processing\DSNStatusResult
   *   An RFC 3463 mail system status identified by the classification. If the
   *   classification fails, NULL is returned.
   *
   * @see http://tools.ietf.org/html/rfc1891
   * @see http://tools.ietf.org/html/rfc3463
   */
  public abstract function classify(Message $message);

  /**
   * {@inheritdoc}
   */
  public function analyze(Message $message) {
    return $this->classify($message);
  }

}
