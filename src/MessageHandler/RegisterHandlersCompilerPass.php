<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageHandler\RegisterHandlersCompilerPass.
 */

namespace Drupal\bounce_processing\MessageHandler;

use Drupal\bounce_processing\RegisterServicesCompilerPass;
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
