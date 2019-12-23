<?php

namespace Drupal\inmail\TypedData;

use Drupal\Core\TypedData\ComplexDataDefinitionBase;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Data definition class for the inmail_mailbox datatype.
 */
class MailboxDefinition extends ComplexDataDefinitionBase {

  // phpcs:disable
  /**
   * {@inheritdoc}
   */
  public static function create($type = 'inmail_mailbox') {
    return parent::create($type);
  }
  // phpcs:enable

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions() {
    return $this->propertyDefinitions = [
      'name' => DataDefinition::create('string')
        ->setLabel('Name'),
      'address' => DataDefinition::create('email')
        ->setLabel('Address')
        ->setRequired(TRUE),
    ];
  }

}
