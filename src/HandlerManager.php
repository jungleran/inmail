<?php
/**
 * @file
 * Contains \Drupal\inmail\HandlerManager.
 */

namespace Drupal\inmail;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Plugin manager for Inmail message handlers.
 *
 * @ingroup handler
 */
class HandlerManager extends DefaultPluginManager implements HandlerManagerInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/inmail/Handler', $namespaces, $module_handler, 'Drupal\inmail\Plugin\inmail\Handler\HandlerInterface', 'Drupal\inmail\Annotation\MessageHandler');
  }

  /**
   * {@inheritdoc}
   */
  public function getFallbackPluginId($plugin_id, array $configuration = array()) {
    return 'broken';
  }

}
