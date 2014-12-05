<?php
/**
 * @file
 * Contains \Drupal\inmail_cfortune\Plugin\inmail\Analyzer\CfortuneAnalyzer.
 */

namespace Drupal\inmail_cfortune\Plugin\inmail\Analyzer;

use cfortune\PHPBounceHandler\BounceHandler;
use Drupal\inmail\DSNStatus;
use Drupal\inmail\Message;
use Drupal\inmail\MessageAnalyzer\Result\AnalyzerResultWritableInterface;
use Drupal\inmail\Plugin\inmail\Analyzer\AnalyzerBase;

/**
 * Message Analyzer wrapper for cfortune's BounceHandler class.
 *
 * The BounceHandler class tries to identify a standardized DSN code and the
 * intended recipient of the original message. If the status is not directly
 * deducible, some pattern-matching for well-known notice strings is applied to
 * cover more cases.
 *
 * The class is maintained by Patrick O'Connell (Rambomst) as a fork of the
 * PHP-Bounce-Handler project: https://github.com/Rambomst/PHP-Bounce-Handler
 *
 * @ingroup analyzer
 *
 * @Analyzer(
 *   id = "cfortune",
 *   label = @Translation("Wrapper for BounceHandler")
 * )
 */
class CfortuneAnalyzer extends AnalyzerBase {

  /**
   * {@inheritdoc}
   */
  public function analyze(Message $message, AnalyzerResultWritableInterface $result) {
    // All operational code is contained in the BounceHandler class.
    $handler = new BounceHandler();

    // Perform the analysis.
    $handler->parse_email($message->getRaw());

    // The status property possibly contains an RFC 3463 status code.
    if ($handler->status) {
      $result->setBounceStatusCode(DSNStatus::parse($handler->status));
    }
    // The recipient property possibly contains the target recipient of the
    // message that bounced.
    if ($handler->recipient) {
      $result->setBounceRecipient(trim($handler->recipient));
    }
  }

}
