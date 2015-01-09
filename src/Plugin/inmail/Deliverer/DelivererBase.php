<?php
/**
 * @file
 * Contains \Plugin\inmail\Deliverer\DelivererBase.
 */

namespace Drupal\inmail\Plugin\inmail\Deliverer;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;

/**
 * Base class for mail deliverers.
 *
 * This provides dumb implementations for most methods, but leaves ::deliver()
 * abstract.
 *
 * @ingroup deliverer
 */
abstract class DelivererBase extends PluginBase implements DelivererInterface {

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
  public function getLabel() {
    return $this->pluginDefinition['label'];
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
