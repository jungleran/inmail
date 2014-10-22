<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\ResultInterface.
 */

namespace Drupal\bounce_processing;

/**
 * A container of message analysis results.
 */
interface ResultInterface {

  /**
   * Returns a human-readable short description of the result.
   *
   * @return string
   *   A short description of the result.
   */
  public function getLabel();

}
