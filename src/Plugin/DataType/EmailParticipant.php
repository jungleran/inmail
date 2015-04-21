<?php
/**
 * @file
 * Contains \Drupal\inmail\Plugin\DataType\EmailParticipant.
 */

namespace Drupal\inmail\Plugin\DataType;

use Drupal\Core\TypedData\Plugin\DataType\Map;

/**
 * Datatype containing a name and an email.
 *
 * @DataType(
 *   id = "inmail_email_participant",
 *   label = @Translation("Email participant"),
 *   definition_class = "Drupal\inmail\TypedData\EmailParticipantDefinition"
 * )
 */
class EmailParticipant extends Map {

}
