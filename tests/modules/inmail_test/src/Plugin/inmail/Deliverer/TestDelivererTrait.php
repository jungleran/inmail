<?php

namespace Drupal\inmail_test\Plugin\inmail\Deliverer;

/**
 * Test trait for Deliverer and Fetchers plugins.
 */
trait TestDelivererTrait {

  /**
   * Returns a state key appropriate for the given state property.
   *
   * @param string $key
   *   Name of key.
   *
   * @return string
   *   An appropriate name for a state property of the deliverer config
   *   associated with this fetcher.
   */
  abstract public function makeStateKey($key);

  /**
   * Returns success state.
   *
   * @return string
   *   The succeeded message key.
   */
  public function getSuccess() {
    return \Drupal::state()->get($this->makeStateKey('success'));
  }

  /**
   * Sets success state.
   *
   * @param string $key
   *   The succeeded message key.
   */
  public function setSuccess($key) {
    \Drupal::state()->set($this->makeStateKey('success'), $key);
  }

}
