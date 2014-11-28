<?php
/**
 * @file
 * Contains \Drupal\inmail\Form\AnalyzerConfigurationForm.
 */

namespace Drupal\inmail\Form;

use Drupal\Core\Config\Entity\ConfigEntityStorageInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\inmail\AnalyzerManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configuration form for analyzers.
 *
 * Analyzer plugins that inherit
 * \Drupal\Component\Plugin\ConfigurablePluginInterface may specify
 * plugin-specific configuration.
 *
 * @ingroup analyzer
 */
class AnalyzerConfigurationForm extends EntityForm {

  /**
   * The message analyzer plugin manager.
   *
   * @var \Drupal\inmail\AnalyzerManagerInterface
   */
  protected $analyzerManager;

  /**
   * The entity storage for analyzer configurations.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface
   */
  protected $storage;

  /**
   * {@inheritdoc}
   */
  public function __construct(AnalyzerManagerInterface $analyzer_manager, ConfigEntityStorageInterface $storage) {
    $this->analyzerManager = $analyzer_manager;
    $this->storage = $storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.inmail.analyzer'),
      $container->get('entity.manager')->getStorage('inmail_analyzer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);
    /** @var \Drupal\inmail\Entity\AnalyzerConfig $entity */
    $entity = $this->getEntity();

    // Load plugin instance.
    /** @var \Drupal\inmail\Plugin\inmail\Analyzer\AnalyzerInterface $plugin */
    $plugin = $this->analyzerManager->createInstance($entity->getPluginId(), $entity->getConfiguration());
    $form_state->set('plugin', $plugin);

    $form['label'] = array(
      '#title' => $this->t('Label'),
      '#type' => 'textfield',
      '#default_value' => $entity->label(),
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $entity->id(),
      '#disabled' => !$entity->isNew(),
      '#machine_name' => array(
        'exists' => array($this, 'exists'),
      ),
    );

    $form['status'] = array(
      '#title' => $this->t('Enabled'),
      '#type' => 'checkbox',
      '#default_value' => TRUE,
    );

    $form['configuration'] = $plugin->buildConfigurationForm(array(), $form_state);

    return $form;
  }

  /**
   * Determines if the analyzer already exists.
   *
   * @param string $id
   *   The analyzer configuration ID.
   *
   * @return bool
   *   TRUE if the analyzer exists, FALSE otherwise.
   */
  public function exists($id) {
    return (!is_null($this->storage->load($id)));
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    /** @var \Drupal\inmail\Plugin\inmail\Analyzer\AnalyzerInterface $plugin */
    $plugin = $form_state->get('plugin');
    $plugin->validateConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    /** @var \Drupal\inmail\Plugin\inmail\Analyzer\AnalyzerInterface $plugin */
    $plugin = $form_state->get('plugin');
    $plugin->submitConfigurationForm($form, $form_state);
    $form_state->setRedirect('inmail.analyzer_list');
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    /** @var \Drupal\inmail\Entity\AnalyzerConfig $entity */
    parent::copyFormValuesToEntity($entity, $form, $form_state);
    $entity->setConfiguration($form_state->get('plugin')->getConfiguration());
  }

}
