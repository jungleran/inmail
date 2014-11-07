<?php
/**
 * @file
 * Contains \Drupal\inmail\MessageHandler\MessageHandlerInterface.
 */

namespace Drupal\inmail\MessageHandler;

use Drupal\inmail\Message;
use Drupal\inmail\MessageAnalyzer\Result\AnalyzerResultReadableInterface;

/**
 * Provides callbacks to execute for an analyzed message.
 */
interface MessageHandlerInterface {

  /**
   * Executes callbacks for an analyzed message.
   *
   * @param \Drupal\inmail\Message $message
   *   The incoming mail message.
   * @param \Drupal\inmail\MessageAnalyzer\Result\AnalyzerResultReadableInterface $result
   *   The analysis result returned by an analyzer. Will be NULL if no analyzer
   *   could provide a result.
   */
  public function invoke(Message $message, AnalyzerResultReadableInterface $result);

}
