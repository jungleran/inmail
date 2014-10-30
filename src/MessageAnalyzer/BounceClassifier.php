<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageAnalyzer\BounceClassifier.
 */

namespace Drupal\bounce_processing\MessageAnalyzer;

use Drupal\bounce_processing\AnalyzerResultInterface;
use Drupal\bounce_processing\Message;

/**
 * Provides methods to determine the type of a message.
 *
 * @todo If AnalyzerResultInterface makes sense (within as well as outside the
 * bounce domain), remove this class, let classifiers inherit
 * MessageAnalyzerInterface, and maybe rename them to Analyzer.
 */
abstract class BounceClassifier implements MessageAnalyzerInterface {

  /**
   * Classifies a message and returns its type.
   *
   * @param \Drupal\bounce_processing\Message $message
   *   An incoming message.
   * @param \Drupal\bounce_processing\AnalyzerResultInterface $result
   *   A result object to report analysis results to.
   *
   * @see http://tools.ietf.org/html/rfc1891
   * @see http://tools.ietf.org/html/rfc3463
   */
  public abstract function classify(Message $message, AnalyzerResultInterface $result);

  /**
   * {@inheritdoc}
   */
  public function analyze(Message $message, AnalyzerResultInterface $result) {
    $this->classify($message, $result);
  }

}
