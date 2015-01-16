<?php
/**
 * @file
 * Contains \Drupal\inmail\Plugin\inmail\Deliverer\DelivererBase.
 */

namespace Drupal\inmail\Plugin\inmail\Deliverer;

use Drupal\Core\Plugin\PluginBase;

/**
 * Base class for mail deliverers.
 *
 * This class should be extended by passive deliverers. Deliverers that can be
 * executed should extend \Drupal\inmail\Plugin\inmail\Deliverer\FetcherBase
 * instead.
 *
 * @ingroup deliverer
 */
abstract class DelivererBase extends PluginBase implements DelivererInterface {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['label'];
  }

}
