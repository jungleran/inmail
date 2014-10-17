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
  public function invoke(Message $message, $type) {
    echo "Classified as $type.\n";
  }

}
