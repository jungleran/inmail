<?php

namespace Drupal\Tests\inmail\Unit\MIME;

use Drupal\Component\Datetime\DateTimePlus;
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
   * Tests the sender getter.
   *
   * @covers ::getFrom
   */
  public function testGetFrom() {
    // Single address.
    $message = new Message(new Header([['name' => 'From', 'body' => 'Foo']]), 'Bar');
    $this->assertEquals('Foo', $message->getFrom());

    if (function_exists('idn_to_utf8')) {
      // Single IDN address.
      $message = new Message(new Header([['name' => 'From', 'body' => 'fooBar@xn--oak-ppa56b.ba']]), 'Bar');
      $this->assertEquals('fooBar@ćošak.ba', $message->getFrom(TRUE));
    }

  }

  /**
   * Tests the recipient getter.
   *
   * @covers ::getTo
   */
  public function testGetTo() {
    // Empty recipient.
    $message = new Message(new Header([[]]), 'I am a body');
    $cc_field = $message->getCC();
    $this->assertEquals(NULL, $cc_field);

    // Single recipient address.
    $message = new Message(new Header([['name' => 'To', 'body' => 'Foo']]), 'Bar');
    $this->assertEquals(['Foo'], $message->getTo());

    // Multiple recipients.
    // @todo Parse recipients and return list.
    $message = new Message(new Header([['name' => 'Cc', 'body' => 'sunshine@example.com, moon@example.com']]), 'I am a body');
    $cc_field = $message->getCC();
    $this->assertEquals(['sunshine@example.com, moon@example.com'], $cc_field);
    // @todo Parse recipients and return list.
    // @todo Test mailbox with display name.

    if (function_exists('idn_to_utf8')) {
      // Single IDN recipient address with decoding.
      $message = new Message(new Header([['name' => 'To', 'body' => 'helloWorld@xn--xample-9ua.com']]), 'Bar');
      $this->assertEquals(['helloWorld@éxample.com'], $message->getTo(TRUE));
    }
  }

  /**
   * Tests the Cc recipients getter.
   *
   * @covers ::getCc
   */
  public function testGetCc() {
    // Empty recipient.
    $message = new Message(new Header([[]]), 'I am a body');
    $cc_field = $message->getCC();
    $this->assertEquals(NULL, $cc_field);

    // Single recipient address.
    $message = new Message(new Header([['name' => 'Cc', 'body' => 'sunshine@example.com']]), 'I am a body');
    $cc_field = $message->getCC();
    $this->assertEquals(['sunshine@example.com'], $cc_field);

    // Multiple recipients.
    // @todo Parse recipients and return list.
    $message = new Message(new Header([['name' => 'Cc', 'body' => 'sunshine@example.com, moon@example.com']]), 'I am a body');
    $cc_field = $message->getCC();
    $this->assertEquals(['sunshine@example.com, moon@example.com'], $cc_field);

    // @todo Also test mailbox with display name.
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
    // By RFC 5322 (https://tools.ietf.org/html/rfc5322#section-3.6,
    // table on p. 21), the only required Header fields are From and Date.
    // In addition, the fields can occur only once per message.
    // Message triggers checking for presence of Date and From fields,
    // as well checking single occurrence of them.
    $message = new Message(new Header([
      ['name' => 'Delivered-To', 'body' => 'alice@example.com'],
      ['name' => 'Received', 'body' => 'Thu, 20 Oct 2016 08:45:02 +0100'],
      ['name' => 'Received', 'body' => 'Fri, 21 Oct 2016 09:55:03 +0200'],
    ]), 'body');
    $this->assertFalse($message->validate());
    // Check that validation error messages exist and it is as expected.
    $this->assertArrayEquals([
      'From' => 'Missing From field.',
      'Date' => 'Missing Date field.',
    ], $message->getValidationErrors());

    // Message contains all necessary fields and only one occurrence of each.
    $message = new Message(new Header([
      ['name' => 'From', 'body' => 'Foo'],
      ['name' => 'Date', 'body' => 'Fri, 21 Oct 2016 09:55:03 +0200'],
    ]), 'body');
    $this->assertTrue($message->validate());
    // Validation error messages should not exist.
    $this->assertEmpty($message->getValidationErrors());

    // Message contains all necessary fields but duplicates.
    $message = new Message(new Header([
      ['name' => 'From', 'body' => 'Foo'],
      ['name' => 'From', 'body' => 'Foo2'],
      ['name' => 'Date', 'body' => 'Thu, 20 Oct 2016 08:45:02 +0100'],
      ['name' => 'Date', 'body' => 'Fri, 21 Oct 2016 09:55:03 +0200'],
      ['name' => 'Date', 'body' => 'Sat, 22 Oct 2016 10:55:04 +0300'],
      ['name' => 'Received', 'body' => 'Thu, 20 Oct 2016 08:45:02 +0100'],
      ['name' => 'Received', 'body' => 'Fri, 21 Oct 2016 09:55:03 +0200'],
    ]), 'body');
    $this->assertFalse($message->validate());
    $this->assertArrayEquals([
      'From' => 'Only one occurrence of From field is allowed. Found 2.',
      'Date' => 'Only one occurrence of Date field is allowed. Found 3.',
    ], $message->getValidationErrors());
  }

}
