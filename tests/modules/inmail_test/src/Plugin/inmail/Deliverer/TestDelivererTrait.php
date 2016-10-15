<?php

namespace Drupal\inmail_test\Plugin\inmail\Deliverer;

/**
 * Test trait for Deliverer and Fetchers plugins.
 */
trait TestDelivererTrait {

  /**
   * Returns success state.
   *
   * @return string
   *   The succeeded message key.
   */
  public static function getSuccess() {
    return \Drupal::state()->get('inmail.test.success');
  }

  /**
   * Resets success state.
   *
   * @return string
   *   The succeeded message key.
   */
  public static function resetSuccess() {
    return \Drupal::state()->set('inmail.test.success', '');
  }

}
