<?php
/**
 * @file
 * Contains \Drupal\inmail_test\Plugin\InmailTestMailCollector.
 */

namespace Drupal\inmail_test\Plugin\Mail;

use Drupal\Core\Mail\Plugin\Mail\TestMailCollector;

/**
 * Works like Mail collector, but does not modify the message body.
 *
 * @see \Drupal\Core\Mail\Plugin\Mail\TestMailCollector
 *
 * @Mail(
 *   id = "inmail_test_mail_collector"
 * )
 */
class InmailTestMailCollector extends TestMailCollector {

  /**
   * {@inheritdoc}
   */
  public function format(array $message) {
    return $message;
  }

}
