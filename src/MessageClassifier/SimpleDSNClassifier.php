<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageClassifier\SimpleDSNClassifier.
 */

namespace Drupal\bounce_processing\MessageClassifier;
use Drupal\bounce_processing\DSNType;
use Drupal\bounce_processing\Message;

/**
 * Extremely simple MessageClassifierInterface instance.
 */
class SimpleDSNClassifier implements MessageClassifierInterface {

  /**
   * {@inheritdoc}
   */
  public function classify(Message $message) {
    // If the message is a DSN.
    if (strpos($message->getHeader('Content-Type'), 'report-type=delivery-status') !== FALSE) {
      // Find a RFC 3463-like code anywhwere in the body.
      if (preg_match('/\s(\d+).(\d+).(\d+)(\s|$)/', $message->getBody(), $matches)) {
        return new DSNType($matches[1], $matches[2], $matches[3]);
      }
    }
    // Otherwise return generic success code.
    return NULL;
  }
}
