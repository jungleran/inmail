<?php
/**
 * @file
 * Contains \Plugin\inmail\Deliverer\DelivererInterface.
 */

namespace Drupal\inmail\Plugin\inmail\Deliverer;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Defines methods for deliverers.
 *
 * @ingroup deliverer
 */
interface DelivererInterface extends ConfigurablePluginInterface, PluginFormInterface, PluginInspectionInterface {

  /**
   * Returns the deliverer label.
   *
   * @return \Drupal\Core\StringTranslation\TranslationWrapper
   *   The deliverer label.
   */
  public function getLabel();

  /**
   * Connects to the configured mailbox and delivers new mail.
   *
   * @return string[]
   *   The delivered messages, in complete raw form.
   */
  public function deliver();

}
