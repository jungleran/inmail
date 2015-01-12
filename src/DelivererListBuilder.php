<?php
/**
 * @file
 * Contains \Drupal\inmail\DelivererListBuilder.
 */

namespace Drupal\inmail;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * List builder for message deliverer configurations.
 *
 * @ingroup deliverer
 */
class DelivererListBuilder extends ConfigEntityListBuilder {

  /**
   * The mail deliverer plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $delivererManager;

  /**
   * Constructs a new DelivererListBuilder.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, PluginManagerInterface $deliverer_manager) {
    parent::__construct($entity_type, $storage);
    $this->delivererManager = $deliverer_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('plugin.manager.inmail.deliverer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $row['label'] = $this->t('Deliverer');
    $row['plugin'] = $this->t('Plugin');
    return $row + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\inmail\Entity\DelivererConfig $entity */
    $plugin_id = $entity->getPluginId();
    if ($this->delivererManager->hasDefinition($plugin_id)) {
      $plugin_label = $this->delivererManager->getDefinition($plugin_id)['label'];
    }
    else {
      $plugin_label = $this->t('Plugin missing');
    }

    $row['label'] = $this->getLabel($entity);
    $row['plugin'] = $plugin_label;
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    // @todo Enable deleting deliverers, https://www.drupal.org/node/2405751
    $operations = parent::getDefaultOperations($entity);
    $operations['edit']['title'] = $this->t('Configure');
    return $operations;
  }
}
