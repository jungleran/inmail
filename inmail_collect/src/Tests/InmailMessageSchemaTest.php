<?php
/**
 * @file
 * Contains \Drupal\inmail_collect\Tests\InmailMessageSchemaTest.
 */

namespace Drupal\inmail_collect\Tests;

use Drupal\collect\Entity\Container;
use Drupal\collect\Entity\SchemaConfig;
use Drupal\inmail\Plugin\DataType\Mailbox;
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
  public static $modules = [
    'inmail_collect',
    'inmail',
    'collect',
    'collect_common',
    'inmail_test',
    'rest',
    'serialization',
  ];

  /**
   * Tests the properties of the schema plugin.
   *
   * @see Drupal\inmail_collect\Plugin\collect\Schema\InmailMessageSchema::evaluate()
   * @see Drupal\inmail_collect\Plugin\collect\Schema\InmailMessageSchema::getStaticPropertyDefinitions()
   */
  public function testProperties() {
    $raw = file_get_contents(\Drupal::root() . '/' . drupal_get_path('module', 'inmail_test') . '/eml/simple-autoreply.eml');
    $container = Container::create([
      'data' => json_encode([
        'raw' => $raw,
      ]),
      'schema_uri' => 'https://www.drupal.org/project/inmail/schema/message',
      'type' => 'application/json',
    ]);

    // Create suggested schema.
    /** @var \Drupal\collect\Schema\SchemaManagerInterface $schema_manager */
    $schema_manager = \Drupal::service('plugin.manager.collect.schema');
    $schema_config = $schema_manager->suggestConfig($container);
    SchemaConfig::create([
      'id' => 'email_schema',
      'label' => $schema_config->label(),
      'plugin_id' => $schema_config->getPluginId(),
      'uri_pattern' => $schema_config->getUriPattern(),
      'properties' => $schema_config->getProperties(),
    ])->save();

    /** @var \Drupal\collect\TypedData\TypedDataProvider $typed_data_provider */
    $typed_data_provider = \Drupal::service('collect.typed_data_provider');
    $data = $typed_data_provider->getTypedData($container);

    // Each property of the schema should map to data in the message.
    $this->assertTrue($data->get('from') instanceof Mailbox);
    $this->assertEqual(['name' => 'Nancy', 'address' => 'nancy@example.com'], $data->get('from')->getValue());
    $this->assertTrue($data->get('to')->get(0) instanceof Mailbox);
    $this->assertEqual([['name' => 'Arild', 'address' => 'arild@example.com']], $data->get('to')->getValue());
    $this->assertTrue($data->get('cc')->get(0) instanceof Mailbox);
    $this->assertEqual([['name' => 'Boss', 'address' => 'boss@example.com']], $data->get('cc')->getValue());
    $this->assertTrue($data->get('bcc')->get(0) instanceof Mailbox);
    $this->assertEqual([['name' => 'Big Brother', 'address' => 'bigbrother@example.com']], $data->get('bcc')->getValue());
    $this->assertEqual('Out of office', $data->get('subject')->getValue());
    $this->assertEqual("Hello\nI'm out of office due to illness", $data->get('body')->getValue());
  }

}
