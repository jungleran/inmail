<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\DummyMessageHandler.
 */

namespace Drupal\bounce_processing;

/**
 * Handles classified messages by logging the type.
 */
class DummyMessageHandler implements MessageHandlerInterface {

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
