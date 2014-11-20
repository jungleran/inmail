<?php
/**
 * @file
 * Contains \Drupal\inmail_mailmute\Plugin\mailmute\SendState\BounceSendstateBase.
 */

namespace Drupal\inmail_mailmute\Plugin\mailmute\SendState;

use Drupal\inmail\DSNStatus;
use Drupal\mailmute\Plugin\mailmute\SendState\SendStateBase;

/**
 * Declares methods for send states that are triggered from bounce messages.
 *
 * @ingroup mailmute
 */
abstract class BounceSendstateBase extends SendStateBase {

  /**
   * Set a descriptive reason for the bounce triggering this state.
   *
   * @param string $reason
   *   The bounce reason.
   *
   * @return static
   *   $this
   */
  public function setReason($reason) {
    $this->configuration['reason'] = (string) $reason;
    return $this;
  }

  /**
   * Returns a descriptive reason for the bounce that triggered this state.
   *
   * @return string|null
   *   The bounce reason, or NULL if none is set.
   */
  public function getReason() {
    return isset($this->configuration['reason']) ? $this->configuration['reason'] : NULL;
  }

  /**
   * Set a status code for the bounce triggering this state.
   *
   * @param \Drupal\inmail\DSNStatus $code
   *   The bounce status object.
   *
   * @return static
   *   $this
   */
  public function setStatus(DSNStatus $code) {
    $this->configuration['code'] = $code;
    return $this;
  }

  /**
   * Returns the status code for the bounce that triggered this state.
   *
   * @return \Drupal\inmail\DSNStatus
   *   The bounce status code, or NULL if none is set.
   */
  public function getStatus() {
    return isset($this->configuration['code']) ? $this->configuration['code'] : NULL;
  }

  /**
   * Returns the status code and a matching label.
   *
   * @return string
   *   The status code, and a label if there is one defined for the code.
   */
  public function getCodeString() {
    if ($status = $this->getStatus()) {
      $args = array('@code' => $status->getCode(), '@label' => $status->getLabel());
      return $this->t($status->getLabel() ? '@code @label' : '@code', $args);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function display() {
    $display['label'] = parent::display();

    $display['bounce'] = array(
      '#type' => 'details',
      '#title' => $this->t('Triggering bounce'),
      '#access' => $this->getStatus() || $this->getReason(),
    );

    $display['bounce']['code'] = array(
      '#type' => 'item',
      '#title' => $this->t('Status code'),
      '#markup' => $this->getCodeString(),
      '#access' => (bool) $this->getStatus(),
    );

    $display['bounce']['reason'] = array(
      '#type' => 'item',
      '#title' => $this->t('Reason message'),
      '#markup' => '<pre>' . $this->getReason() . '</pre>',
      '#access' => (bool) $this->getReason(),
    );

    return $display;
  }

}
