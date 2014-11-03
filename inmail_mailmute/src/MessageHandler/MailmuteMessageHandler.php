<?php
/**
 * @file
 * Contains \Drupal\inmail_mailmute\MessageHandler\MailmuteMessageHandler.
 */

namespace Drupal\inmail_mailmute\MessageHandler;

use Drupal\inmail\AnalyzerResultInterface;
use Drupal\inmail\Message;
use Drupal\inmail\MessageHandler\MessageHandlerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\mailmute\SendStateManagerInterface;

/**
 * Reacts to bounce messages by managing the send state of the bouncing address.
 */
class MailmuteMessageHandler implements MessageHandlerInterface {

  /**
   * The Mailmute send state manager.
   *
   * @var \Drupal\mailmute\SendStateManagerInterface
   */
  protected $sendstateManager;

  /**
   * The Inmail logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $loggerChannel;

  /**
   * Constructs a new MailmuteMessageHandler.
   */
  public function __construct(SendStateManagerInterface $sendstate_manager, LoggerChannelInterface $logger_channel) {
    $this->sendstateManager = $sendstate_manager;
    $this->loggerChannel = $logger_channel;
  }

  /**
   * {@inheritdoc}
   */
  public function invoke(Message $message, AnalyzerResultInterface $result) {
    // Only handle bounces.
    if (empty($result) || !$status_code = $result->getBounceStatusCode()) {
      return;
    }

    // Only handle bounces with an identifiable recipient.
    if (!$address = $result->getBounceRecipient()) {
      // @todo Log the message body or place it in a moderation queue.
      $this->loggerChannel->info('Bounce with status %code received but no recipient identified.', ['%code' => $status_code]);
      return;
    }

    // Only handle bounces with an identifiable recipient that we care about.
    if (!$this->sendstateManager->isManaged($address)) {
      $this->loggerChannel->info('Bounce with status %code received but recipient %address is not managed here.', [
        '%code' => $status_code,
        '%address' => $address,
      ]);
      return;
    }

    // In the case of a "hard bounce", set the send state to a muting state.
    if ($status_code->isPermanentFailure()) {
      $new_state = 'inmail_invalid_address';
      $this->sendstateManager->setState($address, $new_state);
      $this->loggerChannel->info('Bounce with status %code triggered send state transition of %address to %new_state', [
        '%code' => $status_code->getCode(),
        '%address' => $address,
        '%new_state' => $new_state,
      ]);
    }
    else {
      // @todo Handle transient bounces (mailbox full, connection error).
    }
  }

}
