<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageAnalyzer\SimpleDSNClassifier.
 */

namespace Drupal\bounce_processing\MessageAnalyzer;

use Drupal\bounce_processing\AnalyzerResultInterface;
use Drupal\bounce_processing\DSNStatusResult;
use Drupal\bounce_processing\Message;

/**
 * Extremely simple BounceClassifier instance.
 */
class SimpleDSNClassifier extends BounceClassifier {
  // The parsing in this method follows the standards defined in RFC 3464, "An
  // Extensible Message Format for Delivery Status Notifications".

  /**
   * {@inheritdoc}
   */
  public function classify(Message $message, AnalyzerResultInterface $result) {
    // DSN's are declared with the 'Content-Type' header. Example:
    // Content-Type: multipart/report; report-type=delivery-status;
    //   boundary="boundary_2634_73ab76f8"
    if (strpos($message->getHeader('Content-Type'), 'report-type=delivery-status') === FALSE) {
      // Don't care if it does not look like a DSN.
      return;
    }

    // Get the third body part, which has an easy-to-parse, standardized format.
    $parts = $message->getParts();
    if (!isset($parts[2])) {
      // @todo Message malformed - what to do?
      return;
    }
    $machine_part = $parts[2];

    // Parse the 'Status:' pseudo-header.
    if (preg_match('/\nStatus\s*:\s*([245])\.(\d{1,3})\.(\d{1,3})/i', $machine_part, $matches)) {
      $result->setBounceStatusCode(new DSNStatusResult($matches[1], $matches[2], $matches[3]));
    }

    // Parse the 'Final-Recipient:' pseudo-header.
    if (preg_match('/\nFinal-Recipient\s*:[^;]*;\s*(\S*@\S*\.\S*)/i', $machine_part, $matches)) {
      $result->setBounceRecipient($matches[1]);
    }
  }

}
