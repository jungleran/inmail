<?php
/**
 * @file
 * Contains \Drupal\bounce_processing_mailmute\Plugin\Mailmute\SendState\TemporarilyUnreachable.
 */

namespace Drupal\bounce_processing_mailmute\Plugin\Mailmute\SendState;

use Drupal\mailmute\Plugin\Mailmute\SendState\OnHold;

/**
 * Indicates that the address owner is temporarily unreachable.
 *
 * @SendState(
 *   id = "bounce_temporarily_unavailable",
 *   label = @Translation("Temporarily unavailable"),
 * )
 */
class TemporarilyUnreachable extends OnHold {
}
