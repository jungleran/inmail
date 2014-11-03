<?php
/**
 * @file
 * Contains \Drupal\inmail\MessageAnalyzer\SimpleDSNReasonAnalyzer.
 */

namespace Drupal\inmail\MessageAnalyzer;

use Drupal\inmail\AnalyzerResultInterface;
use Drupal\inmail\Message;

/**
 * Extracts the human-readable message from a DSN message.
 *
 * @todo Remove "Simple" prefix or replace with "Standard"?
 */
class SimpleDSNReasonAnalyzer implements MessageAnalyzerInterface {

  /**
   * {@inheritdoc}
   */
  public function analyze(Message $message, AnalyzerResultInterface $result) {
    // Ignore messages that are not DSN.
    if (!$message->isDSN()) {
      return;
    }

    // Save the human-readable bounce reason, without the pseudo-headers part.
    $result->setBounceReason(trim(strstr($message->getParts()[1], "\n\n")));
  }
}
