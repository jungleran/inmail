<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageAnalyzer\SimpleDSNDescriptionAnalyzer.
 */

namespace Drupal\bounce_processing\MessageAnalyzer;

use Drupal\bounce_processing\AnalyzerResultInterface;
use Drupal\bounce_processing\Message;

/**
 * Extracts the human-readable message from a DSN message.
 */
class SimpleDSNDescriptionAnalyzer implements MessageAnalyzerInterface {

  /**
   * {@inheritdoc}
   */
  public function analyze(Message $message, AnalyzerResultInterface $result) {
    // Ignore messages that are not DSN.
    if (!$message->isDSN()) {
      return;
    }

    // Save the human-readable description, without the pseudo-headers part.
    $result->setBounceExplanation(trim(explode("\n\n", $message->getParts()[1], 2)[1]));
  }
}
