<?php
/**
 * @file
 * Contains \Drupal\bounce_processing_mailmute\Plugin\Mailmute\SendState\InvalidAddress.
 */

namespace Drupal\bounce_processing_mailmute\Plugin\Mailmute\SendState;

use Drupal\mailmute\Plugin\Mailmute\SendState\OnHold;

/**
 * Indicates that hard bounces have been received from the address.
 *
 * @SendState(
 *   id = "bounce_invalid_address",
 *   label = @Translation("Invalid address")
 * )
 */
class InvalidAddress extends OnHold {
}
