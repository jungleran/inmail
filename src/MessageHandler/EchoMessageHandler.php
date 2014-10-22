<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageHandler\EchoMessageHandler.
 */

namespace Drupal\bounce_processing\MessageHandler;
use Drupal\bounce_processing\DSNType;
use Drupal\bounce_processing\Message;
use Drupal\bounce_processing\ResultInterface;
use Drupal\Component\Utility\String;

/**
 * Handles classified messages by logging the type.
 */
class EchoMessageHandler implements MessageHandlerInterface {

  /**
   * {@inheritdoc}
   */
  public function invoke(Message $message, ResultInterface $type) {
    if ($type instanceof DSNType) {
      echo String::format("Bounce from @recipient classified as @label\n", array(
        '@recipient' => $type->getRecipient() ?: '(unknown)',
        '@label' => $type->getLabel(),
      ));
    }
    else {
      echo "Unclassified.\n";
    }
  }

}
