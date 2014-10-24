<?php
/**
 * @file
 * Contains \Drupal\bounce_processing_mailmute\MessageHandler\MailmuteMessageHandler.
 */

namespace Drupal\bounce_processing_mailmute\MessageHandler;

use Drupal\bounce_processing\AnalyzerResultInterface;
use Drupal\bounce_processing\DSNStatusResult;
use Drupal\bounce_processing\Message;
use Drupal\bounce_processing\MessageHandler\MessageHandlerInterface;
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
   * The Bounce Processing logger channel.
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
  public function invoke(Message $message, AnalyzerResultInterface $result = NULL) {
    if (!($result instanceof DSNStatusResult)) {
      return;
    }
    if ($address = $result->getRecipient()) {
      if ($this->sendstateManager->isManaged($address)) {
        if ($result->isPermanentFailure()) {
          $new_state = 'bounce_invalid_address';
          $this->sendstateManager->setState($address, $new_state);
          $this->loggerChannel->info('Bounce with status %code triggered send state transition of %address to %new_state', [
            '%code' => $result->getCode(),
            '%address' => $address,
            '%new_state' => $new_state,
          ]);
        }
        // @todo Handle transient bounces (mailbox full, connection error).
      }
      else {
        $this->loggerChannel->info('Bounce with status %code received but recipient %address is not managed here.', [
          '%code' => $result->getCode(),
          '%address' => $address,
        ]);
      }
    }
    else {
      $this->loggerChannel->info('Bounce with status %code received but no recipient identified.', ['%code' => $result->getCode()]);
    }
  }

}
