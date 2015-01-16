<?php
/**
 * @file
 * Contains \Drupal\inmail\Plugin\inmail\Deliverer\FetcherBase.
 */

namespace Drupal\inmail\Plugin\inmail\Deliverer;

use Drupal\Core\Form\FormStateInterface;

/**
 * Base class for mail fetchers.
 *
 * This provides dumb implementations for most methods, but leaves ::fetch() and
 * some configuration methods abstract.
 *
 * @ingroup deliverer
 */
abstract class FetcherBase extends DelivererBase implements FetcherInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    // Merge with defaults.
    parent::__construct($configuration + $this->defaultConfiguration(), $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function isActive() {
    return $this->pluginDefinition['active'];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration + $this->defaultConfiguration();
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // No validation by default.
  }

}
