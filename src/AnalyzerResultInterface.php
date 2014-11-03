<?php
/**
 * @file
 * Contains \Drupal\inmail\AnalyzerResultInterface.
 */

namespace Drupal\inmail;

/**
 * A container of message analysis results.
 *
 * @todo Move into MessageAnalyzer\.
 */
interface AnalyzerResultInterface {
  // @todo Add setters and getters also for properties outside the bounce domain

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

  /**
   * Returns the reported recipient for a bounce message.
   *
   * @return string|null
   *   The address of the intended recipient, or NULL if it has not been
   *   reported.
   */
  public function getBounceRecipient();

  /**
   * Returns the reported status code of a bouce message.
   *
   * @return \Drupal\inmail\DSNStatusResult
   *   The status code, or NULL if it has not been reported.
   */
  public function getBounceStatusCode();

  /**
   * Returns the reason for a bounce.
   *
   * @return string|null
   *   The reason message, in English, or NULL if it has not been reported.
   */
  public function getBounceReason();

}
