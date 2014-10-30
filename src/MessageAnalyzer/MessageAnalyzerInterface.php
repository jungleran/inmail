<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageAnalyzer\MessageAnalyzerInterface.
 */

namespace Drupal\bounce_processing\MessageAnalyzer;

use Drupal\bounce_processing\AnalyzerResultInterface;
use Drupal\bounce_processing\Message;

/**
 * Performs some analysis on a message.
 */
interface MessageAnalyzerInterface {

  /**
   * Analyze the given message.
   *
   * @param \Drupal\bounce_processing\Message $message
   *   A mail message to be analyzed.
   * @param \Drupal\bounce_processing\AnalyzerResultInterface $result
   *   The result object where results should be reported.
   */
  public function analyze(Message $message, AnalyzerResultInterface $result);

}
