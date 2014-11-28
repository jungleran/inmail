<?php
/**
 * @file
 * Contains \Drupal\inmail\Plugin\inmail\Analyzer\AnalyzerInterface.
 */

namespace Drupal\inmail\Plugin\inmail\Analyzer;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\inmail\Message;
use Drupal\inmail\MessageAnalyzer\Result\AnalyzerResultWritableInterface;

/**
 * Performs some analysis on a message.
 *
 * @ingroup analyzer
 */
interface AnalyzerInterface extends ConfigurablePluginInterface, PluginFormInterface, PluginInspectionInterface {

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
