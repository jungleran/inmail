<?php

namespace Drupal\inmail\Plugin\inmail\Deliverer;
use Drupal\inmail\InmailPluginBase;

/**
 * Base class for mail deliverers.
 *
 * This class should be extended by passive deliverers. Deliverers that can be
 * executed should extend \Drupal\inmail\Plugin\inmail\Deliverer\FetcherBase
 * instead.
 *
 * @ingroup deliverer
 */
abstract class DelivererBase extends InmailPluginBase implements DelivererInterface {

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function success($key) {
  }

}
