<?php
/**
 * @file
 * Contains \Drupal\bounce_processing_cfortune\MessageClassifier\CfortuneMessageClassifier.
 */

namespace Drupal\bounce_processing_cfortune\MessageClassifier;

use cfortune\PHPBounceHandler\BounceHandler;
use Drupal\bounce_processing\DSNType;
use Drupal\bounce_processing\Message;
use Drupal\bounce_processing\MessageClassifier\MessageClassifierInterface;

/**
 * Message Classifier wrapper for cfortune's BounceHandler class.
 */
class CfortuneMessageClassifier implements MessageClassifierInterface {

  /**
   * {@inheritdoc}
   */
  public function classify(Message $message) {
    $handler = new BounceHandler();
    $handler->parse_email($message->getRaw());
    if ($handler->status) {
      $type = DSNType::parse($handler->status);
      $type->setRecipient($handler->recipient);
      return $type;
    }
    return NULL;
  }

}
