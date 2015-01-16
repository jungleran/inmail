<?php
/**
 * @file
 * Contains \Drupal\inmail_test\Plugin\inmail\Deliverer\TestDeliverer.
 */

namespace Drupal\inmail_test\Plugin\inmail\Deliverer;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\State\StateInterface;
use Drupal\inmail\Plugin\inmail\Deliverer\DelivererBase;
use Drupal\inmail\Plugin\inmail\Deliverer\FetcherBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Delivers a dummy message and counts invocations.
 *
 * @Deliverer(
 *   id = "test_deliverer",
 *   label = @Translation("Test")
 * )
 */
class TestDeliverer extends FetcherBase implements ContainerFactoryPluginInterface {

  /**
   * Injected site state.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructs a TestDeliverer.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StateInterface $state) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('state'));
  }

  /**
   * {@inheritdoc}
   */
  public function fetch() {
    // Increment invocation count.
    $count = $this->state->get('inmail.test.deliver_count') + 1;
    $this->state->set('inmail.test.deliver_count', $count);

    // Return one minimal message.
    return array("Subject: Dummy message $count\n\nFoo");
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

}
