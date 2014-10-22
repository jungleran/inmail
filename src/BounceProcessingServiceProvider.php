<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\BounceProcessingServiceProvider.
 */

namespace Drupal\bounce_processing;

use Drupal\bounce_processing\MessageAnalyzer\RegisterAnalyzersCompilerPass;
use Drupal\bounce_processing\MessageHandler\RegisterHandlersCompilerPass;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderInterface;

/**
 * Provides Bounce Processing services.
 */
class BounceProcessingServiceProvider implements ServiceProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    $container->addCompilerPass(new RegisterAnalyzersCompilerPass());
    $container->addCompilerPass(new RegisterHandlersCompilerPass());
  }

}
