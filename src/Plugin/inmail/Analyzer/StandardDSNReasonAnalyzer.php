<?php
/**
 * @file
 * Contains \Drupal\inmail\Plugin\inmail\Analyzer\StandardDSNReasonAnalyzer.
 */

namespace Drupal\inmail\Plugin\inmail\Analyzer;

use Drupal\inmail\Message;
use Drupal\inmail\MessageAnalyzer\Result\AnalyzerResultWritableInterface;

/**
 * Extracts the human-readable message from a DSN message.
 *
 * @ingroup analyzer
 *
 * @Analyzer(
 *   id = "dsn_reason",
 *   label = @Translation("Standard DSN Reason Analyzer")
 * )
 */
class StandardDSNReasonAnalyzer extends AnalyzerBase {

  /**
   * {@inheritdoc}
   */
  public function analyze(Message $message, AnalyzerResultWritableInterface $result) {
    // Ignore messages that are not DSN.
    if (!$message->isDSN()) {
      return;
    }

    // Save the human-readable bounce reason, without the pseudo-headers part.
    $parts = $message->getParts();
    if (!isset($parts[1])) {
      // Malformed message, give up.
      return;
    }
    $result->setBounceReason(trim(strstr($message->getParts()[1], "\n\n")));
  }
}
