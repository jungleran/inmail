<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\AnalyzerResultInterface.
 */

namespace Drupal\bounce_processing;

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
   * Report an explanation for a bounce.
   *
   * @param string $explanation
   *   Human-readable information in English explaning why the bounce happened.
   */
  public function setBounceExplanation($explanation);

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
   * @return \Drupal\bounce_processing\DSNStatusResult
   *   The status code, or NULL if it has not been reported.
   */
  public function getBounceStatusCode();

  /**
   * Returns the explanation for a bounce.
   * @return string|null
   *   The explanation message, in English, or NULL if it has not been reported.
   */
  public function getBounceExplanation();

}
