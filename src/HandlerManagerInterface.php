<?php
/**
 * @file
 * Contains \Drupal\inmail\HandlerManagerInterface.
 */

namespace Drupal\inmail;

use Drupal\Component\Plugin\Discovery\DiscoveryInterface;
use Drupal\Component\Plugin\Factory\FactoryInterface;

/**
 * Thin interface for the handler plugin manager.
 */
interface HandlerManagerInterface extends DiscoveryInterface, FactoryInterface {

}
