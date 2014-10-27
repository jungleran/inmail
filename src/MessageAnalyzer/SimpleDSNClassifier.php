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
      // @todo Match against the machine-readable part, not the whole body.
      // Find a RFC 3463-like code anywhwere in the body.
      // @todo always add examples of things you match.
      if (preg_match('/\s([245]).([0-7]).([0-8])(\s|$)/', $message->getBody(), $matches)) {
        $status = new DSNStatusResult($matches[1], $matches[2], $matches[3]);

        // Check for VERP Return-Path.
        $return_path = explode('@', \Drupal::config('system.settings')->get('site.mail'));
        // Match the modified Return-Path and put the parts of the recipient
        // address in $matches.
        // @todo $return_path could probably break the regex here?
        if (preg_match(':^' . $return_path[0] . '\+(.*)=(.*)@' . $return_path[1] . '$:', $message->getHeader('To'), $matches)) {
          $status->setRecipient($matches[1] . '@' . $matches[2]);
        }

        // Return the status object.
        return $status;
      }
    }
    // Otherwise return generic success code.
    return NULL;
  }

}
