<?php
/**
 * @file
 * Contains \Drupal\inmail_test\Plugin\InmailTestMailCollector.
 */

namespace Drupal\inmail_test\Plugin\Mail;

use Drupal\Core\Mail\Plugin\Mail\TestMailCollector;

/**
 * Class InmailTestMailCollector
 *
 * @package Drupal\inmail_test\Plugin\Mail
 *
 * @Mail(
 *   id = "inmail_test_mail_collector",
 *   label = @Translation("Inmail mail collector"),
 *   description = @Translation("Works like Mail collector, but does not modify the message body.")
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
