<?php
/**
 * @file
 * Contains \Drupal\inmail\TypedData\EmailParticipantDefinition.
 */

namespace Drupal\inmail\TypedData;

use Drupal\Core\TypedData\ComplexDataDefinitionBase;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Data definition class for the inmail_email_participant datatype.
 */
class EmailParticipantDefinition extends ComplexDataDefinitionBase {
  /**
   * {@inheritdoc}
   */
  public static function create($type = 'inmail_email_participant') {
    return parent::create($type);
  }

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions() {
    return $this->propertyDefinitions = [
      'name' => DataDefinition::create('string')
        ->setLabel('Name'),
      'address' => DataDefinition::create('email')
        ->setLabel('Email')
        ->setRequired(TRUE),
    ];
  }

}
