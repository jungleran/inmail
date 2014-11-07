<?php
/**
 * @file
 * Contains \Drupal\inmail\MessageAnalyzer\Result\AnalyzerResultWritableInterface.
 */

namespace Drupal\inmail\MessageAnalyzer\Result;

use Drupal\inmail\DSNStatusResult;

/**
 * A container of message analysis results.
 */
interface AnalyzerResultWritableInterface {
  // @todo Add setters also for properties outside the bounce domain

  /**
   * Report the intended recipient for a bounce message.
   *
   * @param string $recipient
   *   The address of the recipient.
   */
  public function setBounceRecipient($recipient);

  /**
   * Report the status code of a bounce message.
   *
   * @param DSNStatusResult $code
   *   A status code.
   */
  public function setBounceStatusCode(DSNStatusResult $code);

  /**
   * Report the reason for a bounce.
   *
   * @param string $reason
   *   Human-readable information in English explaning why the bounce happened.
   */
  public function setBounceReason($reason);

}
