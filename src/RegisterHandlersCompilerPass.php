<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\RegisterHandlersCompilerPass.
 */

namespace Drupal\bounce_processing;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds tagged message handler services to the processor.
 */
class RegisterHandlersCompilerPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    $definition = $container->getDefinition('bounce.processor');
    foreach ($container->findTaggedServiceIds('bounce.handler') as $service => $attributes) {
      $definition->addMethodCall('addHandler', [new Reference($service)]);
    }
  }

}
