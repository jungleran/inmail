<?php

namespace Drupal\Tests\inmail\Kernel;

use Drupal\inmail\TypedData\MailboxDefinition;
use Drupal\KernelTests\KernelTestBase;
use Drupal\inmail\MIME\Entity;
use Drupal\inmail\MIME\Header;

/**
 * Tests inmail datatypes.
 *
 * @group inmail
 */
class InmailDataTypeTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['inmail'];

  /**
   * Tests the inmail_mailbox datatype.
   */
  public function testMailbox() {
    $typed_data_manager = \Drupal::typedDataManager();

    // Test the definition create method.
    $data_definition = MailboxDefinition::create();
    $this->assertEqual($data_definition, $typed_data_manager->createDataDefinition('inmail_mailbox'));

    // Access the properties.
    /** @var \Drupal\inmail\Plugin\DataType\Mailbox $data */
    $data = $typed_data_manager->create($data_definition, [
      'name' => 'Alice',
      'address' => 'alice@example.com',
    ]);
    $this->assertEqual('Alice', $data->get('name')->getValue());
    $this->assertEqual('alice@example.com', $data->get('address')->getValue());

    // Address is required.
    $violations = $typed_data_manager->create($data_definition, ['name' => 'Alice'])->validate();
    $this->assertEqual(1, $violations->count());
    $this->assertEqual('address', $violations->get(0)->getPropertyPath());
    $this->assertEqual('This value should not be null.', $violations->get(0)->getMessage());

    // Name is not required.
    $violations = $typed_data_manager->create($data_definition, ['address' => 'alice@example.com'])->validate();
    $this->assertEqual(0, $violations->count());

    // Address should have valid format.
    $violations = $typed_data_manager->create($data_definition, ['address' => 'alice'])->validate();
    $this->assertEqual(1, $violations->count());
    $this->assertEqual('address', $violations->get(0)->getPropertyPath());
    $this->assertEqual('This value is not a valid email address.', $violations->get(0)->getMessage());
  }

  /**
   * Tests the function inmail_message_get_attachment_file_size()
   *
   * @dataProvider providedData
   */
  public function testAttachmentFileSize($attachment, $size) {
    self::assertEquals($size, inmail_message_get_attachment_file_size($attachment->getBody(), $attachment->getContentTransferEncoding()));
  }

  /**
   * Provides data to test inmail_message_get_attachment_file_size() with.
   *
   * @return array
   */
  public function providedData() {
    return
    [
      [
        new Entity(new Header(), $this->randomString(1)),
        '1 byte',
      ],
      [
        new Entity(new Header(), $this->randomString(1024)),
        '1 KB',
      ],
      [
        new Entity(new Header(), $this->randomString(2022)),
        '1.97 KB',
      ],
      [
        new Entity(new Header([
          ['name' => 'Content-Transfer-Encoding', 'body' => 'base64'],
        ]), $this->randomString(20480)),
        '20 KB',
      ],
    ];
  }
}
