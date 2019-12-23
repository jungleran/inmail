<?php

namespace Drupal\Tests\inmail\Traits;

use Drupal\inmail\Entity\DelivererConfig;

/**
 * Provides common helper methods for Deliverer testing.
 */
trait DelivererTestTrait {

  /**
   * Creates a Deliverer.
   *
   * @param string $plugin
   *   The plugin name.
   *
   * @return \Drupal\inmail\Entity\DelivererConfig
   *   The deliverer.
   */
  protected function createTestDeliverer($plugin = 'test_deliverer') {
    $id = $this->randomMachineName();
    /** @var \Drupal\inmail\Entity\DelivererConfig $deliverer */
    $deliverer = DelivererConfig::create([
      'id' => $id,
      'plugin' => $plugin,
    ]);
    $deliverer->setConfiguration(['config_id' => $id]);

    return $deliverer;
  }

  /**
   * Asserts success report with $key.
   *
   * @param \Drupal\inmail\Entity\DelivererConfig $deliverer
   *   The deliverer.
   * @param string $key
   *   The success key.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function assertSuccess(DelivererConfig $deliverer, $key) {
    $plugin = $deliverer->getPluginInstance();
    $this->assertEqual($plugin->getSuccess(), $key);
  }

}
