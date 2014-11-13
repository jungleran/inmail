<?php
/**
 * @file
 * Contains \Drupal\inmail_mailmute\Plugin\Mailmute\SendState\InvalidAddress.
 */

namespace Drupal\inmail_mailmute\Plugin\Mailmute\SendState;

use Drupal\mailmute\Plugin\Mailmute\SendState\OnHold;

/**
 * Indicates that hard bounces have been received from the address.
 *
 * @SendState(
 *   id = "inmail_invalid_address",
 *   label = @Translation("Invalid address"),
 *   mute = true,
 *   admin = true
 * )
 */
class InvalidAddress extends OnHold {
}
