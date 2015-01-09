<?php
/**
 * @file
 * Contains \Drupal\inmail\Entity\AnalyzerConfig.
 */

namespace Drupal\inmail\Entity;

/**
 * Message analyzer configuration entity.
 *
 * This entity type is for storing the configuration of an analyzer plugin.
 *
 * @ingroup analyzer
 *
 * @todo Add config_prefix in https://www.drupal.org/node/2379773
 *
 * @ConfigEntityType(
 *   id = "inmail_analyzer",
 *   label = @Translation("Message analyzer"),
 *   admin_permission = "administer inmail",
 *   handlers = {
 *     "form" = {
 *       "default" = "Drupal\inmail\Form\AnalyzerConfigurationForm"
 *     },
 *     "list_builder" = "Drupal\inmail\AnalyzerListBuilder"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "status" = "status",
 *     "weight" = "weight"
 *   },
 *   links = {
 *     "edit-form" = "entity.inmail_analyzer.edit_form",
 *     "enable" = "entity.inmail_analyzer.enable",
 *     "disable" = "entity.inmail_analyzer.disable"
 *   }
 * )
 */
class AnalyzerConfig extends PluginConfigEntity {

  /**
   * The weight of the analyzer configuration.
   *
   * Analyzers with lower weights are invoked before those with higher weights.
   *
   * @var int
   */
  protected $weight;

}
