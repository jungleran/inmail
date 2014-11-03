<?php
/**
 * @file
 * Contains \Drupal\inmail\MessageAnalyzer\BounceClassifier.
 */

namespace Drupal\inmail\MessageAnalyzer;

use Drupal\inmail\AnalyzerResultInterface;
use Drupal\inmail\Message;

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
   * @param \Drupal\inmail\Message $message
   *   An incoming message.
   * @param \Drupal\inmail\AnalyzerResultInterface $result
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
