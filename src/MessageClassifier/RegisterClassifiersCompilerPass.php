<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageClassifier\RegisterClassifiersCompilerPass.
 */

namespace Drupal\bounce_processing\MessageClassifier;

use Drupal\bounce_processing\RegisterServicesCompilerPass;
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
