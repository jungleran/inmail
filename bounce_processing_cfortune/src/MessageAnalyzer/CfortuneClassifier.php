<?php
/**
 * @file
 * Contains \Drupal\bounce_processing_cfortune\MessageAnalyzer\CfortuneClassifier.
 */

namespace Drupal\bounce_processing_cfortune\MessageAnalyzer;

use cfortune\PHPBounceHandler\BounceHandler;
use Drupal\bounce_processing\AnalyzerResultInterface;
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
  public function classify(Message $message, AnalyzerResultInterface $result) {
    $handler = new BounceHandler();
    $handler->parse_email($message->getRaw());
    if ($handler->status) {
      $result->setBounceStatusCode(DSNStatusResult::parse($handler->status));
      $result->setBounceRecipient(trim($handler->recipient));
    }
  }

}
