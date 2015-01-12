<?php
/**
 * @file
 * Contains \Drupal\inmail\Entity\DelivererConfig.
 */

namespace Drupal\inmail\Entity;

/**
 * Mail deliverer configuration entity.
 *
 * @ingroup deliverer
 *
 * @ConfigEntityType(
 *   id = "inmail_deliverer",
 *   label = @Translation("Mail deliverer"),
 *   admin_permission = "administer inmail",
 *   config_prefix = "deliverer",
 *   handlers = {
 *     "form" = {
 *       "default" = "Drupal\inmail\Form\DelivererConfigurationForm",
 *       "add" = "Drupal\inmail\Form\DelivererConfigurationForm",
 *       "delete" = "Drupal\inmail\Form\DelivererDeleteForm"
 *     },
 *     "list_builder" = "Drupal\inmail\DelivererListBuilder"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "status" = "status"
 *   },
 *   links = {
 *     "edit-form" = "entity.inmail_deliverer.edit_form",
 *     "delete-form" = "entity.inmail_deliverer.delete_form",
 *     "enable" = "entity.inmail_deliverer.enable",
 *     "disable" = "entity.inmail_deliverer.disable"
 *   }
 * )
 */
class DelivererConfig extends PluginConfigEntity {
}
