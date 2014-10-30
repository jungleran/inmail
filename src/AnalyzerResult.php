<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\AnalyzerResult.
 */

namespace Drupal\bounce_processing;

/**
 * Contains analyzer results.
 *
 * The setter methods only have effect the first time they are called, so values
 * are only writable once.
 */
class AnalyzerResult implements AnalyzerResultInterface {

  protected $properties = array();

  /**
   * {@inheritdoc}
   */
  public function setBounceRecipient($recipient) {
    $this->set('bounce_recipient', $recipient);
  }

  /**
   * {@inheritdoc}
   */
  public function setBounceStatusCode(DSNStatusResult $code) {
    $this->set('bounce_status_code', $code);
  }

  /**
   * {@inheritdoc}
   */
  public function setBounceExplanation($explanation) {
    $this->set('bounce_explanation', $explanation);
  }

  /**
   * {@inheritdoc}
   */
  public function getBounceRecipient() {
    return $this->get('bounce_recipient');
  }

  /**
   * {@inheritdoc}
   */
  public function getBounceStatusCode() {
    return $this->get('bounce_status_code');
  }

  /**
   * {@inheritdoc}
   */
  public function getBounceExplanation() {
    return $this->get('bounce_explanation');
  }

  /**
   * Set an arbitrary property.
   *
   * The property is only modified if it has not already been set.
   *
   * @param string $key
   *   The name of the property to set.
   * @param mixed $value
   *   The value of the property.
   */
  protected function set($key, $value) {
    if (!isset($this->properties[$key])) {
      $this->properties[$key] = $value;
    }
  }

  /**
   * Get an arbitrary property.
   *
   * @param string $key
   *   The name of the property to get.
   *
   * @return mixed
   *   The property value, or NULL if it has not been set.
   */
  protected function get($key) {
    if (isset($this->properties[$key])) {
      return $this->properties[$key];
    }
    return NULL;
  }
}
