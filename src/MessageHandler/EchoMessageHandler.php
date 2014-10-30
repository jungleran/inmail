<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageHandler\EchoMessageHandler.
 */

namespace Drupal\bounce_processing\MessageHandler;

use Drupal\bounce_processing\DSNStatusResult;
use Drupal\bounce_processing\Message;
use Drupal\bounce_processing\AnalyzerResultInterface;
use Drupal\Component\Utility\String;

/**
 * Handles classified messages by logging the type.
 */
class EchoMessageHandler implements MessageHandlerInterface {

  /**
   * {@inheritdoc}
   */
  public function invoke(Message $message, AnalyzerResultInterface $result) {
    if ($code = $result->getBounceStatusCode()) {
      echo String::format("Bounce from @recipient classified as @code\n", array(
        '@recipient' => $result->getBounceRecipient() ?: '(unknown)',
        '@code' => $code->getCode(),
      ));
    }
    else {
      echo "Unclassified.\n";
    }
  }

}
