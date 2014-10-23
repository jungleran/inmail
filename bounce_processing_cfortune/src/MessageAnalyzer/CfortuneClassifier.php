<?php
/**
 * @file
 * Contains \Drupal\bounce_processing_cfortune\MessageAnalyzer\CfortuneClassifier.
 */

namespace Drupal\bounce_processing_cfortune\MessageAnalyzer;

use cfortune\PHPBounceHandler\BounceHandler;
use Drupal\bounce_processing\DSNStatusResult;
use Drupal\bounce_processing\Message;
use Drupal\bounce_processing\MessageAnalyzer\BounceClassifier;

/**
 * Message Classifier wrapper for cfortune's BounceHandler class.
 */
class CfortuneClassifier extends BounceClassifier {

  /**
   * {@inheritdoc}
   */
  public function classify(Message $message) {
    $handler = new BounceHandler();
    $handler->parse_email($message->getRaw());
    if ($handler->status) {
      $status = DSNStatusResult::parse($handler->status);
      $status->setRecipient($handler->recipient);
      return $status;
    }
    return NULL;
  }

}
