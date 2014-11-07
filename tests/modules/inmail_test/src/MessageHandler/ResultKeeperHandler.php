<?php
/**
 * @file
 * Contains \Drupal\inmail_test\MessageHandler\ResultKeeperHandler.
 */

namespace Drupal\inmail_test\MessageHandler;

use Drupal\inmail\Message;
use Drupal\inmail\MessageAnalyzer\Result\AnalyzerResultReadableInterface;
use Drupal\inmail\MessageHandler\MessageHandlerInterface;

/**
 * Stores analysis results to let them be easily evaluated by tests.
 */
class ResultKeeperHandler implements MessageHandlerInterface {

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
