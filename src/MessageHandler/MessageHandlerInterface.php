<?php
/**
 * @file
 * Contains \Drupal\inmail\MessageHandler\MessageHandlerInterface.
 */

namespace Drupal\inmail\MessageHandler;

use Drupal\inmail\AnalyzerResultInterface;
use Drupal\inmail\Message;

/**
 * Provides callbacks to execute for an analyzed message.
 */
interface MessageHandlerInterface {

  /**
   * Executes callbacks for an analyzed message.
   *
   * @param \Drupal\inmail\Message $message
   *   The incoming mail message.
   * @param \Drupal\inmail\AnalyzerResultInterface $result
   *   The analysis result returned by an analyzer. Will be NULL if no analyzer
   *   could provide a result.
   */
  public function invoke(Message $message, AnalyzerResultInterface $result);

}
