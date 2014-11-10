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
 * @todo Create HandlerManagerInterface.
 */
class HandlerManager extends DefaultPluginManager {

  /**
   * The handler plugin objects.
   *
   * @var \Drupal\inmail\Plugin\inmail\Handler\HandlerInterface[]
   */
  protected $handlers;

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/inmail/Handler', $namespaces, $module_handler, 'Drupal\inmail\Plugin\inmail\Handler\HandlerInterface', 'Drupal\inmail\Annotation\MessageHandler');
  }

  /**
   * Returns all discovered handler plugins.
   *
   * @return \Drupal\inmail\Plugin\inmail\Handler\HandlerInterface[]
   *   An array of message handlers.
   */
  public function getHandlers() {
    if (!isset($this->handlers)) {
      foreach ($this->getDefinitions() as $definition) {
        $this->handlers[$definition['id']] = $this->createInstance($definition['id']);
      }
    }
    return $this->handlers;
  }

  /**
   * Returns the handler instance with the given ID.
   *
   * @param string $plugin_id
   *   A handler plugin ID.
   *
   * @return \Drupal\inmail\Plugin\inmail\Handler\HandlerInterface
   *   The handler object.
   */
  public function getHandler($plugin_id) {
    return $this->getHandlers()[$plugin_id];
  }

}
