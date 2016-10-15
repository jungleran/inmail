<?php

namespace Drupal\inmail\Tests;
use Drupal\inmail\Entity\DelivererConfig;
use Drupal\inmail_test\Plugin\inmail\Deliverer\TestDeliverer;

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
    $deliverer = DelivererConfig::create([
      'id' => 'test',
      'plugin' => 'test_deliverer',
    ]);
    $deliverer->setConfiguration(['config_id' => 'test']);

    return $deliverer;
  }

  /**
   * Asserts success report with $key.
   */
  protected function assertSuccess($key) {
    $this->assertEqual(TestDeliverer::getSuccess(), $key);
  }

}
