<?php
/**
 * @file
 * Contains \Drupal\inmail_cfortune\MessageAnalyzer\CfortuneClassifier.
 */

namespace Drupal\inmail_cfortune\MessageAnalyzer;

use cfortune\PHPBounceHandler\BounceHandler;
use Drupal\inmail\AnalyzerResultInterface;
use Drupal\inmail\DSNStatusResult;
use Drupal\inmail\Message;
use Drupal\inmail\MessageAnalyzer\BounceClassifier;

/**
 * Message Classifier wrapper for cfortune's BounceHandler class.
 */
class CfortuneClassifier extends BounceClassifier {

  /**
   * {@inheritdoc}
   */
  public function classify(Message $message, AnalyzerResultInterface $result) {
    // All operational code is contained in the BounceHandler class.
    $handler = new BounceHandler();

    // Perform the analysis.
    $handler->parse_email($message->getRaw());

    // The status property possibly contains an RFC 3463 status code.
    if ($handler->status) {
      $result->setBounceStatusCode(DSNStatusResult::parse($handler->status));
    }
    // The recipient property possibly contains the target recipient of the
    // message that bounced.
    if ($handler->recipient) {
      $result->setBounceRecipient(trim($handler->recipient));
    }
  }

}
