<?php
/**
 * @file
 * Contains \Drupal\bounce_processing_mailmute\MessageHandler\MailmuteMessageHandler.
 */

namespace Drupal\bounce_processing_mailmute\MessageHandler;

use Drupal\bounce_processing\DSNType;
use Drupal\bounce_processing\Message;
use Drupal\bounce_processing\MessageHandler\MessageHandlerInterface;
use Drupal\bounce_processing\ResultInterface;

/**
 * Reacts to bounce messages by managing the send state of the bouncing address.
 */
class MailmuteMessageHandler implements MessageHandlerInterface {

  /**
   * @var \Drupal\bounce_processing\MessageHandler\SendStateManagerInterface
   */
  protected $sendStateManager;

  /**
   * {@inheritdoc}
   */
  public function invoke(Message $message, ResultInterface $type) {
    if ($type instanceof DSNType && $address = $type->getRecipient()) {
      // @todo Let Mailmute calculate transitions.
      if ($user = user_load_by_mail($address)) {
        $user->field_sendstate = $type->isFailure();
        $user->save();
      }
    }
  }

}
