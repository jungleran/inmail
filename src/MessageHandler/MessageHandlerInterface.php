<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageHandler\MessageHandlerInterface.
 */

namespace Drupal\bounce_processing\MessageHandler;

use Drupal\bounce_processing\Message;
use Drupal\bounce_processing\ResultInterface;

/**
 * Provides callbacks to execute for an analyzed message.
 */
interface MessageHandlerInterface {

  /**
   * Executes callbacks for an analyzed message.
   *
   * @param \Drupal\bounce_processing\Message $message
   *   The incoming mail message.
   * @param \Drupal\bounce_processing\ResultInterface $result
   *   The analysis result returned by an analyzer. Will be NULL if no analyzer
   *   could provide a result.
   */
  public function invoke(Message $message, ResultInterface $result);

}
