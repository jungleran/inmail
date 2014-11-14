<?php
/**
 * @file
 * Contains \Drupal\inmail_cfortune\MessageAnalyzer\CfortuneAnalyzer.
 */

namespace Drupal\inmail_cfortune\MessageAnalyzer;

use cfortune\PHPBounceHandler\BounceHandler;
use Drupal\inmail\DSNStatus;
use Drupal\inmail\Message;
use Drupal\inmail\Messageanalyzer\MessageAnalyzerInterface;
use Drupal\inmail\MessageAnalyzer\Result\AnalyzerResultWritableInterface;

/**
 * Message Analyzer wrapper for cfortune's BounceHandler class.
 */
class CfortuneAnalyzer implements MessageAnalyzerInterface {

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
