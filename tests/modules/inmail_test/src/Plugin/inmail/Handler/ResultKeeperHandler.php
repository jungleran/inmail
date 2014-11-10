<?php
/**
 * @file
 * Contains \Drupal\inmail_test\MessageHandler\ResultKeeperHandler.
 */

namespace Drupal\inmail_test\Plugin\inmail\Handler;

use Drupal\inmail\Message;
use Drupal\inmail\MessageAnalyzer\Result\AnalyzerResultReadableInterface;
use Drupal\inmail\Plugin\inmail\Handler\HandlerInterface;

/**
 * Stores analysis results to let them be easily evaluated by tests.
 *
 * @MessageHandler(
 *   id = "result_keeper"
 * )
 */
class ResultKeeperHandler implements HandlerInterface {

  /**
   * The processed message.
   *
   * @var \Drupal\inmail\Message
   */
  public $message;

  /**
   * The analysis result.
   *
   * @var \Drupal\inmail\MessageAnalyzer\Result\AnalyzerResultWritableInterface
   */
  public $result;

  /**
   * {@inheritdoc}
   */
  public function invoke(Message $message, AnalyzerResultReadableInterface $result) {
    $this->message = $message;
    $this->result = $result;
  }
}
