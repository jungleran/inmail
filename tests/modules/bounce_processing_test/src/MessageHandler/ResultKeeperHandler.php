<?php
/**
 * @file
 * Contains \Drupal\bounce_processing_test\MessageHandler\ResultKeeperHandler.
 */

namespace Drupal\bounce_processing_test\MessageHandler;

use Drupal\bounce_processing\AnalyzerResultInterface;
use Drupal\bounce_processing\Message;
use Drupal\bounce_processing\MessageHandler\MessageHandlerInterface;

/**
 * Stores analysis results to let them be easily evaluated by tests.
 */
class ResultKeeperHandler implements MessageHandlerInterface {

  /**
   * The processed message.
   *
   * @var \Drupal\bounce_processing\Message
   */
  public $message;

  /**
   * The analysis result.
   *
   * @var \Drupal\bounce_processing\AnalyzerResultInterface
   */
  public $result;

  /**
   * {@inheritdoc}
   */
  public function invoke(Message $message, AnalyzerResultInterface $result) {
    $this->message = $message;
    $this->result = $result;
  }
}
