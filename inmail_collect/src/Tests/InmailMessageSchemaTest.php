<?php
/**
 * @file
 * Contains \Drupal\inmail_collect\Tests\InmailMessageSchemaTest.
 */

namespace Drupal\inmail_collect\Tests;

use Drupal\collect\Entity\Container;
use Drupal\collect\TypedData\CollectDataDefinition;
use Drupal\simpletest\KernelTestBase;

/**
 * Tests the Inmail schema.
 *
 * @group inmail
 */
class InmailMessageSchemaTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['inmail_collect', 'inmail', 'collect', 'inmail_test', 'rest', 'serialization'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(['inmail_collect']);
  }

  /**
   * Tests the properties of the schema plugin.
   *
   * @see Drupal\inmail_collect\Plugin\collect\Schema\InmailMessageSchema::getDataProperty()
   * @see Drupal\inmail_collect\Plugin\collect\Schema\InmailMessageSchema::getPropertyDefinitions()
   */
  public function testProperties() {
    $raw = file_get_contents(\Drupal::root() . '/' . drupal_get_path('module', 'inmail_test') . '/eml/simple-autoreply.eml');
    $container = Container::create([
      'data' => [
        'data' => json_encode([
          'raw' => $raw,
        ]),
        'schema' => 'https://www.drupal.org/project/inmail/schema/message',
        'type' => 'application/json',
      ],
    ]);

    /** @var \Drupal\collect\TypedData\TypedDataProvider $typed_data_provider */
    $typed_data_provider = \Drupal::service('collect.typed_data_provider');
    $data = $typed_data_provider->getTypedData($container->getDataItem());

    // Each property of the schema should map to data in the message.
    $this->assertEqual('Nancy <nancy@example.com>', $data->get('from'));
    $this->assertEqual('nancy@example.com', $data->get('from_address'));
    $this->assertEqual('Arild <arild@example.com>', $data->get('to'));
    $this->assertEqual('arild@example.com', $data->get('to_address'));
    $this->assertEqual('Out of office', $data->get('subject'));
    $this->assertEqual("Hello\nI'm out of office due to illness", $data->get('body'));
  }

}
