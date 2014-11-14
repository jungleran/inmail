<?php
/**
 * @file
 * Contains \Drupal\inmail\Entity\Handler.
 */

namespace Drupal\inmail\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Message handler configuration entity.
 *
 * @ingroup handler
 *
 * @ConfigEntityType(
 *   id = "inmail_handler",
 *   label = @Translation("Message handler"),
 *   admin_permission = "administer inmail",
 *   handlers = {
 *     "form" = {
 *       "default" = "Drupal\inmail\Form\HandlerConfigurationForm",
 *     },
 *     "list_builder" = "Drupal\inmail\HandlerListBuilder"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "status" = "status"
 *   },
 *   links = {
 *     "edit-form" = "entity.inmail_handler.edit_form",
 *     "enable" = "entity.inmail_handler.enable",
 *     "disable" = "entity.inmail_handler.disable"
 *   }
 * )
 */
class Handler extends ConfigEntityBase {

  /**
   * A machine name for the handler configuration.
   *
   * @var string
   */
  protected $id;

  /**
   * A translatable, human-readable name for the handler configuration.
   *
   * @var string
   */
  protected $label;

  /**
   * The ID of the handler plugin for this configuration.
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
   * Returns the handler plugin ID.
   *
   * @return string
   *   The machine name of the plugin for this handler.
   */
  public function getPluginId() {
    return $this->plugin;
  }

  /**
   * Returns the plugin configuration stored for this handler.
   *
   * @return array
   *   The plugin configuration. Its properties are defined by the associated
   *   plugin.
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * Replaces the configuration stored for this handler.
   *
   * @param array $configuration
   *   New plugin configuraion. Should match the properties defined by the
   *   plugin referenced by ::$plugin.
   */
  public function setConfiguration($configuration) {
    $this->configuration = $configuration;
  }

}
