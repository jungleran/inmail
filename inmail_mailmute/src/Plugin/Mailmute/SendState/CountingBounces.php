<?php
/**
 * @file
 * Contains \Drupal\inmail_mailmute\Plugin\Mailmute\SendState\CountingBounces.
 */

namespace Drupal\inmail_mailmute\Plugin\Mailmute\SendState;

use Drupal\mailmute\Plugin\Mailmute\SendState\Send;

/**
 * Class CountingBounces
 *
 * @SendState(
 *   id = "inmail_counting",
 *   label = @Translation("Counting soft bounces"),
 *   mute = false,
 *   admin = true
 * )
 */
class CountingBounces extends Send {

  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    // Trigger exception if threshold is invalid or not set.
    $this->setThreshold($configuration['threshold']);
  }

  /**
   * {@inheritdoc}
   */
  public function display() {
    return array(
      '#markup' => $this->t('%label (%count of %threshold received)', array(
        '%label' => $this->getPluginDefinition()['label'],
        '%count' => $this->configuration['count'],
        '%threshold' => $this->configuration['threshold'],
      )),
    );
  }

  /**
   * Returns the current number of received bounces.
   *
   * @return int
   *   The number of received bounces.
   */
  public function getCount() {
    return isset($this->configuration['count']) ? $this->configuration['count'] : 0;
  }

  /**
   * Set the current count of received bounces.
   *
   * @param int $count
   *   The new number of bounces.
   */
  public function setCount($count) {
    $this->configuration['count'] = $count;
  }

  /**
   * Increment the current count of received bounces by 1.
   */
  public function increment() {
    $this->setCount($this->getCount() + 1);
  }

  /**
   * Returns the accepted number of bounces before address should be muted.
   *
   * @return int
   *   An integer threshold value.
   */
  public function getThreshold() {
    // This value must be set.
    return $this->configuration['threshold'];
  }

  /**
   * Set the accepted number of bounces.
   *
   * @param int $threshold
   *   The accepted number of bounces.
   *
   * @throws \InvalidArgumentException
   *   If $threshold is not a positive integer.
   */
  public function setThreshold($threshold) {
    if (intval($threshold) <= 0) {
      throw new \InvalidArgumentException('Threshold must be a positive integer.');
    }
    $this->configuration['threshold'] = intval($threshold);
  }

}
