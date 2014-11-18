<?php
/**
 * @file
 * Contains \Drupal\inmail\Plugin\Mail\DirectMail.
 */

namespace Drupal\inmail\Plugin\Mail;

use Drupal\Core\Mail\MailInterface;

/**
 * Sends a message with the native mail() function and without modification.
 *
 * @Mail(
 *   id = "inmail_direct",
 *   label = @Translation("Direct"),
 *   description = @Translation("Sends a message with the native mail() function and without modification.")
 * )
 */
class DirectMail implements MailInterface {

  /**
   * {@inheritdoc}
   */
  public function format(array $message) {
    // Join the body array into one string.
    $message['body'] = implode("\n\n", $message['body']);

    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function mail(array $message) {
    // Headers are $message['raw_headers'], explanation in inmail_mail().
    return (bool) mail(
      $message['to'],
      $message['subject'],
      $message['body'],
      $message['raw_headers']
    );
  }
}
