<?php
/**
 * @file
 * Contains \Drupal\inmail\MessageAnalyzer\MessageAnalyzerInterface.
 */

namespace Drupal\inmail\MessageAnalyzer;

use Drupal\inmail\AnalyzerResultInterface;
use Drupal\inmail\Message;

/**
 * Performs some analysis on a message.
 */
interface MessageAnalyzerInterface {

  /**
   * Analyze the given message.
   *
   * @param \Drupal\inmail\Message $message
   *   A mail message to be analyzed.
   * @param \Drupal\inmail\AnalyzerResultInterface $result
   *   The result object where results should be reported.
   */
  public function analyze(Message $message, AnalyzerResultInterface $result);

}
