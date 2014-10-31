<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\RegisterServicesCompilerPass.
 */

namespace Drupal\bounce_processing;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass helper providing a method to register tagged services.
 */
abstract class RegisterServicesCompilerPass implements CompilerPassInterface {

  /**
   * Adds method call on a host service for tagged services.
   *
   * Use this to e.g. register tagged services with some containing service.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
   *   The container to process.
   * @param string $host_service
   *   The id of the service on which the method should be called.
   * @param string $tag
   *   The tag name applied to the other services.
   * @param string $method
   *   The name of the method to call.
   */
  protected static function addMethodCallWithTaggedServices(ContainerBuilder $container, $host_service, $tag, $method) {
    $host_definition = $container->getDefinition($host_service);
    $services = array();

    // Retrieve registered tagged services from the container.
    foreach ($container->findTaggedServiceIds($tag) as $id => $attributes) {
      $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
      $services[$priority][] = new Reference($id);
    }

    // Add the registered tagged services to the host service.
    foreach (static::sort($services) as $service) {
      $host_definition->addMethodCall($method, array($service));
    }
  }

  /**
   * Sorts by priority.
   *
   * Order services from highest priority number to lowest (reverse sorting).
   *
   * @param array $services
   *   A nested array keyed on priority number. For each priority number, the
   *   value is an array of Symfony\Component\DependencyInjection\Reference
   *   objects.
   *
   * @return \Symfony\Component\DependencyInjection\Reference[]
   *   A flattened array of Reference objects from $services, ordered from high
   *   to low priority.
   */
  protected static function sort($services) {
    $sorted = array();
    krsort($services);

    // Flatten the array.
    foreach ($services as $a) {
      $sorted = array_merge($sorted, $a);
    }

    return $sorted;
  }

}
