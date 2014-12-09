<?php
/**
 * @file
 * Contains \Drupal\inmail\Plugin\inmail\Handler\HandlerInterface.
 */

namespace Drupal\inmail\Plugin\inmail\Handler;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\inmail\Message;
use Drupal\inmail\ProcessorResultInterface;

/**
 * Provides a callback to execute for an analyzed message.
 *
 * @ingroup handler
 */
interface HandlerInterface extends ConfigurablePluginInterface, PluginFormInterface, PluginInspectionInterface {

  /**
   * Returns helpful explanation for using and configuring the handler.
   *
   * @return array
   *   A build array structure with a description of the handler.
   */
  public function help();

  /**
   * Executes callbacks for an analyzed message.
   *
   * @param \Drupal\inmail\Message $message
   *   The incoming mail message.
   * @param \Drupal\inmail\ProcessorResultInterface $processor_result
   *   The analysis result returned by an analyzer. Will be NULL if no analyzer
   *   could provide a result.
   */
  public function invoke(Message $message, ProcessorResultInterface $processor_result);

}
