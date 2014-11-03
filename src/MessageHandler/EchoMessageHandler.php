<?php
/**
 * @file
 * Contains \Drupal\inmail\MessageHandler\EchoMessageHandler.
 */

namespace Drupal\inmail\MessageHandler;

use Drupal\inmail\AnalyzerResultInterface;
use Drupal\inmail\Message;
use Drupal\Component\Utility\String;

/**
 * Handles classified bounce messages by echoing the status code.
 *
 * This might only make sense in a console environment like drush.
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
      echo "Not a bounce.\n";
    }
  }

}
