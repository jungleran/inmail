<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageAnalyzer\RegisterAnalyzersCompilerPass.
 */

namespace Drupal\bounce_processing\MessageAnalyzer;

use Drupal\bounce_processing\RegisterServicesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Adds tagged message analyzer services to the processor.
 */
class RegisterAnalyzersCompilerPass extends RegisterServicesCompilerPass {

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    static::addMethodCallWithTaggedServices($container, 'bounce.processor', 'bounce.analyzer', 'addAnalyzer');
  }

}
