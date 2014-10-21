<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\RegisterClassifiersCompilerPass.
 */

namespace Drupal\bounce_processing;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds tagged message classifier services to the processor.
 */
class RegisterClassifiersCompilerPass implements CompilerPassInterface {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    $definition = $container->getDefinition('bounce.processor');
    foreach ($container->findTaggedServiceIds('bounce.classifier') as $service => $attributes) {
      $definition->addMethodCall('addClassifier', [new Reference($service)]);
    }
  }

}
