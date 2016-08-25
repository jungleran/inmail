<?php

namespace Drupal\Tests\inmail\Unit\MIME;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\inmail\MIME\Entity;
use Drupal\inmail\MIME\Header;
use Drupal\inmail\MIME\Message;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the MIME Message class.
 *
 * @coversDefaultClass \Drupal\inmail\MIME\Message
 *
 * @group inmail
 */
class MessageTest extends UnitTestCase {

  /**
   * Tests the message ID getter.
   *
   * @covers ::getMessageId
   */
  public function testGetMessageId() {
    $message = new Message(new Header([['name' => 'Message-ID', 'body' => '<Foo@example.com>']]), 'Bar');
    $this->assertEquals('<Foo@example.com>', $message->getMessageId());
  }

  /**
   * Tests the subject getter.
   *
   * @covers ::getSubject
   */
  public function testGetSubject() {
    $message = new Message(new Header([['name' => 'Subject', 'body' => 'Foo']]), 'Bar');
    $this->assertEquals('Foo', $message->getSubject());
  }

  /**
   * Tests the recipient getter.
   *
   * @covers ::getTo
   */
  public function testGetTo() {
    $message = new Message(new Header([['name' => 'To', 'body' => 'Foo']]), 'Bar');
    $this->assertEquals('Foo', $message->getTo());
  }

  /**
   * Tests the sender getter.
   *
   * @covers ::getFrom
   */
  public function testGetFrom() {
    $message = new Message(new Header([['name' => 'From', 'body' => 'Foo']]), 'Bar');
    $this->assertEquals('Foo', $message->getFrom());
  }

  /**
   * Tests the 'Received' date getter.
   *
   * @covers ::getReceivedDate
   */
  public function testGetReceivedDate() {
    $message = new Message(new Header([
      ['name' => 'Received', 'body' => 'blah; Thu, 29 Jan 2015 15:43:04 +0100'],
    ]), 'I am a body');

    $expected_date = new DateTimePlus('Thu, 29 Jan 2015 15:43:04 +0100');

    $this->assertEquals($expected_date, $message->getReceivedDate());
  }

  /**
   * Tests the message is valid and contains necessary fields.
   */
  public function testValidation() {
    // By RFC (https://tools.ietf.org/html/rfc5322#section-3.6, table on p. 21),
    // the only required Header fields are From and Date. In addition,
    // the fields can occur only once per message.

    // Message triggers checking for presence of Received and From fields,
    // as well checking single occurrence of them.
    $message = new Message(new Header([
      ['name' => 'Received', 'body' => 'Mon, 22 Aug 2016 09:24:00 +0100'],
    ]), 'body');
    $this->assertFalse($message->validate());
    // Check that validation error messages exists and it is as expected.
    $this->assertEquals('Missing From Field', $message->getValidationErrors()['From']);

    // Message contains all necessary fields and only one occurrence of each.
    $message = new Message(new Header([
      ['name' => 'From', 'body' => 'Foo'],
      ['name' => 'Received', 'body' => 'Tue, 23 Aug 2016 17:48:6 +0600'],
    ]), 'body');
    $this->assertTrue($message->validate());
    // Validation error messages should not exist.
    $this->assertTrue(empty($message->getValidationErrors()));

    // Message contains all necessary fields but duplicates.
    $message = new Message(new Header([
      ['name' => 'From', 'body' => 'Foo'],
      ['name' => 'From', 'body' => 'Foo2'],
      ['name' => 'Received', 'body' => 'Tue, 23 Aug 2016 17:48:6 +0600'],
    ]), 'body');
    $this->assertFalse($message->validate());
    $this->assertEquals('2 From Fields', $message->getValidationErrors()['From']);
  }

}
