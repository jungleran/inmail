<?php
/**
 * @file
 * Contains \Drupal\inmail\Plugin\inmail\Analyzer\StandardDSNReasonAnalyzer.
 */

namespace Drupal\inmail\Plugin\inmail\Analyzer;

use Drupal\inmail\BounceAnalyzerResult;
use Drupal\inmail\Message;
use Drupal\inmail\ProcessorResultInterface;

/**
 * Extracts the human-readable message from a DSN message.
 *
 * @todo issue use MIME parser here again.
 * @see Message
 *
 * @todo Drop standard intro texts https://www.drupal.org/node/2379917
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
  public function analyze(Message $message, ProcessorResultInterface $processor_result) {
    $processor_result->addAnalyzerResult(BounceAnalyzerResult::TOPIC, new BounceAnalyzerResult());
    /** @var \Drupal\inmail\BounceAnalyzerResult $result */
    $result = $processor_result->getAnalyzerResult(BounceAnalyzerResult::TOPIC);

    // Ignore messages that are not DSN.
    if (!$message->isDSN()) {
      return;
    }

    // Save the human readable bounce reason, without the pseudo headers part.
    $parts = $message->getParts();
    if (!isset($parts[1])) {
      // Malformed message, give up.
      return;
    }
    // Drop the header from the entity and proceed with body.
    $body = strstr($parts[1], "\n\n");
    $result->setReason(trim($body));
  }
}
