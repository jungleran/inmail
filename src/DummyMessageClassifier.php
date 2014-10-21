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
    // If the message is a DSN.
    if (strpos($message->getHeader('Content-Type'), 'report-type=delivery-status') !== FALSE) {
      // Find a RFC 3463-like code anywhwere in the body.
      if (preg_match('/\s(\d+).(\d+).(\d+)(\s|$)/', $message->getBody(), $matches)) {
        return new DSNType($matches[1], $matches[2], $matches[3]);
      }
    }
    // Otherwise return generic success code.
    return new DSNType(2, 0, 0);
  }
}
