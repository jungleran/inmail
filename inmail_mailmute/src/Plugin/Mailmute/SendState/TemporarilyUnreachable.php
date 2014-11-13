<?php
/**
 * @file
 * Contains \Drupal\inmail_mailmute\Plugin\Mailmute\SendState\TemporarilyUnreachable.
 */

namespace Drupal\inmail_mailmute\Plugin\Mailmute\SendState;

use Drupal\mailmute\Plugin\Mailmute\SendState\OnHold;

/**
 * Indicates that the address owner is temporarily unreachable.
 *
 * @SendState(
 *   id = "inmail_temporarily_unreachable",
 *   label = @Translation("Temporarily unreachable"),
 *   mute = true,
 *   admin = true
 * )
 */
class TemporarilyUnreachable extends OnHold {
}
