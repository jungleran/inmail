<?php
/**
 * @file
 * Contains \Drupal\inmail\MessageAnalyzer\Result\AnalyzerResultReadableInterface.
 */

namespace Drupal\inmail\MessageAnalyzer\Result;

/**
 * Defines methods to read reported message analysis results.
 *
 * @ingroup analyzer
 */
interface AnalyzerResultReadableInterface {
  // @todo Add getters also for properties outside the bounce domain: https://www.drupal.org/node/2381023

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
   * @return \Drupal\inmail\DSNStatus
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
