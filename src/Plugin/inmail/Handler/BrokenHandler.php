<?php
/**
 * @file
 * Contains \Drupal\inmail\Plugin\inmail\Handler\BrokenHandler.
 */

namespace Drupal\inmail\Plugin\inmail\Handler;

use Drupal\inmail\Message;
use Drupal\inmail\MessageAnalyzer\Result\AnalyzerResultReadableInterface;

/**
 * Fallback handler plugin.
 *
 * If you create a handler configuration, then uninstall the module that
 * provides the handler, then this will show up as the handler for that
 * configuration.
 *
 * @MessageHandler(
 *   id = "broken",
 *   label = @Translation("Missing handler")
 * )
 */
class BrokenHandler extends HandlerBase {

  /**
   * {@inheritdoc}
   */
  public function invoke(Message $message, AnalyzerResultReadableInterface $result) {
    // Do nothing.
  }

}
