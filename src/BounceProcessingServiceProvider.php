<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\BounceProcessingServiceProvider.
 */

namespace Drupal\bounce_processing;

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
    $container->addCompilerPass(new RegisterClassifiersCompilerPass());
    $container->addCompilerPass(new RegisterHandlersCompilerPass());
  }

}
