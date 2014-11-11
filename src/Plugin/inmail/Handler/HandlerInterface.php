<?php
/**
 * @file
 * Contains \Drupal\inmail\MessageHandler\HandlerInterface.
 */

namespace Drupal\inmail\Plugin\inmail\Handler;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\inmail\Message;
use Drupal\inmail\MessageAnalyzer\Result\AnalyzerResultReadableInterface;

/**
 * Provides callbacks to execute for an analyzed message.
 */
interface HandlerInterface extends ConfigurablePluginInterface, PluginFormInterface {

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
