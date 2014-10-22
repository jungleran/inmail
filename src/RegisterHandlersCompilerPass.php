<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\RegisterHandlersCompilerPass.
 */

namespace Drupal\bounce_processing;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Adds tagged message handler services to the processor.
 */
class RegisterHandlersCompilerPass extends RegisterServicesCompilerPass {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    $this->addMethodCallWithTaggedServices($container, 'bounce.processor', 'bounce.handler', 'addHandler');
  }

}
