<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageAnalyzer\SimpleDSNClassifier.
 */

namespace Drupal\bounce_processing\MessageAnalyzer;

use Drupal\bounce_processing\DSNStatusResult;
use Drupal\bounce_processing\Message;

/**
 * Extremely simple BounceClassifier instance.
 */
class SimpleDSNClassifier extends BounceClassifier {

  /**
   * {@inheritdoc}
   */
  public function classify(Message $message) {
    // If the message is a DSN.
    // @todo example dsn header.
    if (strpos($message->getHeader('Content-Type'), 'report-type=delivery-status') !== FALSE) {
      // Find a RFC 3463-like code anywhwere in the body.
      // @todo always add examples of things you match.
      if (preg_match('/\s([245]).([0-7]).([0-8])(\s|$)/', $message->getBody(), $matches)) {
        // @todo make sure the range of the DSN is correct.
        return new DSNStatusResult($matches[1], $matches[2], $matches[3]);
      }
    }
    // Otherwise return generic success code.
    return NULL;
  }

}
