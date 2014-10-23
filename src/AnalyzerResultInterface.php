<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\AnalyzerResultInterface.
 */

namespace Drupal\bounce_processing;

/**
 * A container of message analysis results.
 */
interface AnalyzerResultInterface {

  /**
   * Returns a human-readable short description of the result.
   *
   * @return string
   *   A short description of the result.
   */
  public function getLabel();

}
