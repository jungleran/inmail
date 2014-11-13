<?php
/**
 * @file
 * Contains \Drupal\inmail\HandlerListBuilder.
 */

namespace Drupal\inmail;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * List builder for meassge handler configurations.
 */
class HandlerListBuilder extends ConfigEntityListBuilder {

  /**
   * The message handler plugin manager.
   *
   * @var \Drupal\inmail\HandlerManagerInterface
   */
  protected $handlerManager;

  /**
   * Constructs a new HandlerListBuilder.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, HandlerManagerInterface $handler_manager) {
    parent::__construct($entity_type, $storage);
    $this->handlerManager = $handler_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('plugin.manager.inmail.handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $row['label'] = $this->t('Handler');
    $row['plugin'] = $this->t('Plugin');
    return $row + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['plugin'] = $this->handlerManager->getDefinition($entity->getPluginId())['label'];
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    $operations['edit']['title'] = $this->t('Configure');
    return $operations;
  }
}