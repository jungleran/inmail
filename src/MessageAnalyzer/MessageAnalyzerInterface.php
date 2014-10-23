<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageAnalyzer\MessageAnalyzerInterface.
 */

namespace Drupal\bounce_processing\MessageAnalyzer;

use Drupal\bounce_processing\Message;

/**
 * Performs some analysis on a message.
 */
interface MessageAnalyzerInterface {

  /**
   * Analyze the given message.
   *
   * @param Message $message
   *   A mail message to be analyzed.
   *
   * @return \Drupal\bounce_processing\AnalyzerResultInterface
   *   The result of the analysis.
   */
  public function analyze(Message $message);

}
