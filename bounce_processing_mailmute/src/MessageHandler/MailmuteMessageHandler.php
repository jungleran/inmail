<?php
/**
 * @file
 * Contains \Drupal\bounce_processing_mailmute\MessageHandler\MailmuteMessageHandler.
 */

namespace Drupal\bounce_processing_mailmute\MessageHandler;

use Drupal\bounce_processing\DSNStatusResult;
use Drupal\bounce_processing\Message;
use Drupal\bounce_processing\MessageHandler\MessageHandlerInterface;
use Drupal\bounce_processing\AnalyzerResultInterface;

/**
 * Reacts to bounce messages by managing the send state of the bouncing address.
 */
class MailmuteMessageHandler implements MessageHandlerInterface {

  /**
   * {@inheritdoc}
   */
  public function invoke(Message $message, AnalyzerResultInterface $result) {
    // @todo Don't let pass if no recipient. Log.
    if ($result instanceof DSNStatusResult && $address = $result->getRecipient()) {
      // @todo use same logic like user/subscriber through email field.
      if ($user = user_load_by_mail($address)) {
        // @todo I'm a state machine!
        $user->field_sendstate = $result->isFailure();
        $user->save();
      }
    }
  }

}
