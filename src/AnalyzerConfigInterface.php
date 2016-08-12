<?php

namespace Drupal\inmail;

/**
 * Thin interface for the analyzer config manager.
 *
 * @package Drupal\inmail
 */
interface AnalyzerConfigInterface extends InmailPluginConfigInterface {

  /**
   * Returns the weight
   *
   * @return int
   *   The weight of analyzer configuration
   */
  public function getWeight();

  /**
   *
   * Sets the weight
   *
   * @param $weight
   *   The weight of analyzer configurations
   */
  public function setWeight($weight);

}
