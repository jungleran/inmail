<?php
/**
 * @file
 * Contains \Drupal\inmail_mailmute\Plugin\Mailmute\SendState\PersistentSend.
 */

namespace Drupal\inmail_mailmute\Plugin\Mailmute\SendState;

/**
 * Indicates that messages should be sent, and no transitions allowed.
 *
 * This is useful to protect against bluff bounces which could otherwise be used
 * to mute innocent users. A better protection is always to enable VERP or
 * similar recipient identification, if possible.
 *
 * @SendState(
 *   id = "persistent_send",
 *   label = @Translation("Persistent send"),
 *   mute = false,
 *   admin = true
 * )
 */
class PersistentSend extends BounceSendstateBase {
}
