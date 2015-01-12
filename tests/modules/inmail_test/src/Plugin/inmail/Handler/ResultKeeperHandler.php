<?php
/**
 * @file
 * Contains \Drupal\inmail_test\Plugin\inmail\Handler\ResultKeeperHandler.
 */

namespace Drupal\inmail_test\Plugin\inmail\Handler;

use Drupal\inmail\MIME\EntityInterface;
use Drupal\inmail\Plugin\inmail\Handler\HandlerBase;
use Drupal\inmail\ProcessorResultInterface;

/**
 * Stores analysis results to let them be easily evaluated by tests.
 *
 * @Handler(
 *   id = "result_keeper",
 *   label = @Translation("Result keeper"),
 *   description = @Translation("Stores analysis results to let them be easily evaluated by tests.")
 * )
 */
class ResultKeeperHandler extends HandlerBase {

  /**
   * The processed message.
   *
   * @var \Drupal\inmail\Message
   */
  public static $message;

  /**
   * The analysis result.
   *
   * @var \Drupal\inmail\ProcessorResultInterface
   */
  public static $result;

  /**
   * {@inheritdoc}
   */
  public function help() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function invoke(EntityInterface $message, ProcessorResultInterface $processor_result) {
    static::$message = $message;
    static::$result = $processor_result;
  }

}
