<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageHandler\EchoMessageHandler.
 */

namespace Drupal\bounce_processing\MessageHandler;
use Drupal\bounce_processing\Message;
use Drupal\bounce_processing\MessageTypeInterface;

/**
 * Handles classified messages by logging the type.
 */
class EchoMessageHandler implements MessageHandlerInterface {

  /**
   * {@inheritdoc}
   */
  public function invoke(Message $message, MessageTypeInterface $type) {
    if (isset($type)) {
      echo "Classified as " . $type->getLabel() . "\n";
    }
    else {
      echo "Unclassified.";
    }
  }

}
