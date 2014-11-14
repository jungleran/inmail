<?php
/**
 * @file
 * Contains \Drupal\inmail\MessageAnalyzer\StandardDSNReasonAnalyzer.
 */

namespace Drupal\inmail\MessageAnalyzer;

use Drupal\inmail\Message;
use Drupal\inmail\MessageAnalyzer\Result\AnalyzerResultWritableInterface;

/**
 * Extracts the human-readable message from a DSN message.
 *
 * @ingroup analyzer
 */
class StandardDSNReasonAnalyzer implements MessageAnalyzerInterface {

  /**
   * {@inheritdoc}
   */
  public function analyze(Message $message, AnalyzerResultWritableInterface $result) {
    // Ignore messages that are not DSN.
    if (!$message->isDSN()) {
      return;
    }

    // Save the human-readable bounce reason, without the pseudo-headers part.
    $result->setBounceReason(trim(strstr($message->getParts()[1], "\n\n")));
  }
}
