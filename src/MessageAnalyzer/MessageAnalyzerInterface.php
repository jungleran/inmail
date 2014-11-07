<?php
/**
 * @file
 * Contains \Drupal\inmail\MessageAnalyzer\MessageAnalyzerInterface.
 */

namespace Drupal\inmail\MessageAnalyzer;

use Drupal\inmail\Message;
use Drupal\inmail\MessageAnalyzer\Result\AnalyzerResultWritableInterface;

/**
 * Performs some analysis on a message.
 */
interface MessageAnalyzerInterface {

  /**
   * Analyze the given message.
   *
   * @param \Drupal\inmail\Message $message
   *   A mail message to be analyzed.
   * @param \Drupal\inmail\MessageAnalyzer\Result\AnalyzerResultWritableInterface $result
   *   The result object where results should be reported.
   */
  public function analyze(Message $message, AnalyzerResultWritableInterface $result);

}
