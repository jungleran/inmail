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
   * Adds method call on a containing service for tagged services.
   *
   * Use this to e.g. register tagged services with some containing service.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
   *   The container to process.
   * @param string $service
   *   The id of the service on which the method should be called.
   * @param string $tag
   *   The tag name applied to the other services.
   * @param string $method
   *   The name of the method to call.
   */
  public function addMethodCallWithTaggedServices(ContainerBuilder $container, $service, $tag, $method) {
    $definition = $container->getDefinition($service);
    $resolvers = array();

    // Retrieve registered tagged services from the container.
    foreach ($container->findTaggedServiceIds($tag) as $id => $attributes) {
      $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
      $resolvers[$priority][] = new Reference($id);
    }

    // Add the registered concrete EntityResolvers to the ChainEntityResolver.
    foreach ($this->sort($resolvers) as $resolver) {
      $definition->addMethodCall($method, array($resolver));
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
  protected function sort($services) {
    $sorted = array();
    krsort($services);

    // Flatten the array.
    foreach ($services as $a) {
      $sorted = array_merge($sorted, $a);
    }

    return $sorted;
  }

}
