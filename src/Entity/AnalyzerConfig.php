<?php
/**
 * @file
 * Contains \Drupal\inmail\Entity\AnalyzerConfig.
 */

namespace Drupal\inmail\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Message analyzer configuration entity.
 *
 * This entity type is for storing the configuration of an analyzer plugin.
 *
 * @ingroup analyzer
 *
 * @ConfigEntityType(
 *   id = "inmail_analyzer",
 *   label = @Translation("Message analyzer"),
 *   admin_permission = "administer inmail",
 *   handlers = {
 *     "form" = {
 *       "default" = "Drupal\inmail\Form\AnalyzerConfigurationForm",
 *     },
 *     "list_builder" = "Drupal\inmail\AnalyzerListBuilder"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "status" = "status"
 *   },
 *   links = {
 *     "edit-form" = "entity.inmail_analyzer.edit_form",
 *     "enable" = "entity.inmail_analyzer.enable",
 *     "disable" = "entity.inmail_analyzer.disable"
 *   }
 * )
 */
class AnalyzerConfig extends ConfigEntityBase {

  /**
   * The machine name of the analyzer configuration.
   *
   * @var string
   */
  protected $id;

  /**
   * The translatable, human-readable name of the analyzer configuration.
   *
   * @var string
   */
  protected $label;

  /**
   * The ID of the analyzer plugin for this configuration.
   *
   * @var string
   */
  protected $plugin;

  /**
   * The configuration for the plugin.
   *
   * @var array
   */
  protected $configuration = array();

  /**
   * Returns the analyzer plugin ID.
   *
   * @return string
   *   The machine name of the plugin for this analyzer.
   */
  public function getPluginId() {
    return $this->plugin;
  }

  /**
   * Returns the plugin configuration stored for this analyzer.
   *
   * @return array
   *   The plugin configuration. Its properties are defined by the associated
   *   plugin.
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * Replaces the configuration stored for this analyzer.
   *
   * @param array $configuration
   *   New plugin configuraion. Should match the properties defined by the
   *   plugin referenced by ::$plugin.
   *
   * @return $this
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
    return $this;
  }

}
