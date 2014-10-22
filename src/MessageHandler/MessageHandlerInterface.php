<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageHandler\MessageHandlerInterface.
 */

namespace Drupal\bounce_processing\MessageHandler;
use Drupal\bounce_processing\Message;
use Drupal\bounce_processing\MessageTypeInterface;

/**
 * Provides callbacks to execute for a classified message.
 */
interface MessageHandlerInterface {

  /**
   * Executes callbacks for a classified message.
   *
   * @param \Drupal\bounce_processing\Message $message
   *   The incoming mail message.
   * @param \Drupal\bounce_processing\MessageTypeInterface $type
   *   The message type as determined by a classifier. Will be NULL if no
   *   classifier returned anything.
   */
  public function invoke(Message $message, MessageTypeInterface $type);

}
