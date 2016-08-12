<?php

namespace Drupal\inmail;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Interface for Plugin configuration
 *
 * @package Drupal\inmail
 */
interface InmailPluginConfigInterface extends ConfigEntityInterface {

  /**
   *Returns the plugin ID.
   *
   * @return string
   *   The machine name of this plugin
   */
  public function getPluginId();

  /**
   * Sets the plugin ID.
   *
   * @param $plugin
   */
  public function setPluginId($plugin);

  /**
   * Returns the configuration stored for this plugin.
   *
   * @return array
   *   The plugin configuration. Its properties are defined by associated
   */
  public function getConfiguration();

  /**
   * Replaces the configuration stored for this plugin.
   *
   * @param array
   *   New plugin configuration. Should match the properties defined by the
   *   plugin referenced by ::$plugin
   *
   * @return  $this
   */
  public function setConfiguration(array $configuration);

}
