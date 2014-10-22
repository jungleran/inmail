<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\RegisterClassifiersCompilerPass.
 */

namespace Drupal\bounce_processing;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Adds tagged message classifier services to the processor.
 */
class RegisterClassifiersCompilerPass extends RegisterServicesCompilerPass {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    $this->addMethodCallWithTaggedServices($container, 'bounce.processor', 'bounce.classifier', 'addClassifier');
  }

}
