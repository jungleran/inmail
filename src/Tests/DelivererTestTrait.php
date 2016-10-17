<?php

namespace Drupal\inmail\Tests;

use Drupal\inmail\Entity\DelivererConfig;
use Drupal\inmail_test\Plugin\inmail\Deliverer\TestDeliverer;
use Drupal\inmail_test\Plugin\inmail\Deliverer\TestFetcher;

/**
 * Provides common helper methods for Deliverer testing.
 */
trait DelivererTestTrait {

  /**
   * Creates a Deliverer.
   *
   * @return DelivererConfig
   *   The deliverer.
   */
  protected function createTestDeliverer() {
    $id = $this->randomMachineName();
    $deliverer = DelivererConfig::create([
      'id' => $id,
      'plugin' => 'test_deliverer',
    ]);
    $deliverer->setConfiguration(['config_id' => $id]);

    return $deliverer;
  }

  /**
   * Asserts success report with $key.
   *
   * @param DelivererConfig $deliverer
   *   The deliverer.
   *
   * @param string $key
   *   The success key.
   */
  protected function assertSuccess($deliverer, $key) {
    $plugin = $deliverer->getPluginInstance();
    $this->assertEqual($plugin->getSuccess(), $key);
  }

}
