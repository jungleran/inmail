<?php

namespace Drupal\Tests\inmail\Unit\MIME;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\inmail\MIME\MimeHeader;
use Drupal\inmail\MIME\MimeMessage;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the MimeMessage class.
 *
 * @coversDefaultClass \Drupal\inmail\MIME\MimeMessage
 *
 * @group inmail
 */
class MimeMessageTest extends UnitTestCase {

  /**
   * Tests the message ID getter.
   *
   * @covers ::getMessageId
   */
  public function testGetMessageId() {
    $message = new MimeMessage(new MimeHeader([['name' => 'Message-ID', 'body' => '<Foo@example.com>']]), 'Bar');
    $this->assertEquals('<Foo@example.com>', $message->getMessageId());
  }

  /**
   * Tests the subject getter.
   *
   * @covers ::getSubject
   */
  public function testGetSubject() {
    $message = new MimeMessage(new MimeHeader([['name' => 'Subject', 'body' => 'Foo']]), 'Bar');
    $this->assertEquals('Foo', $message->getSubject());
  }

  /**
   * Tests the sender getter.
   *
   * @covers ::getFrom
   */
  public function testGetFrom() {
    // Single address.
    $message = new MimeMessage(new MimeHeader([['name' => 'From', 'body' => 'foo@example.com']]), 'Bar');
    $this->assertEquals('foo@example.com', $message->getFrom()['address']);

    if (function_exists('idn_to_utf8')) {
      // Single IDN address.
      $message = new MimeMessage(new MimeHeader([['name' => 'From', 'body' => 'fooBar@xn--oak-ppa56b.ba']]), 'Bar');
      $this->assertEquals('fooBar@ćošak.ba', $message->getFrom(TRUE)['address']);
    }

  }

  /**
   * Tests the recipient getter.
   *
   * @covers ::getTo
   */
  public function testGetTo() {
    // Empty recipient.
    $message = new MimeMessage(new MimeHeader([[]]), 'I am a body');
    $cc_field = $message->getCC();
    $this->assertEquals(NULL, $cc_field);

    // Single recipient address.
    $message = new MimeMessage(new MimeHeader([['name' => 'To', 'body' => 'foo@example.com']]), 'Bar');
    $this->assertEquals('foo@example.com', $message->getTo()[0]['address']);

    // Multiple recipients.
    // @todo Parse recipients and return list.
    $message = new MimeMessage(new MimeHeader([['name' => 'Cc', 'body' => 'sunshine@example.com, moon@example.com']]), 'I am a body');
    $cc_field = $message->getCC();
    $this->assertEquals(['sunshine@example.com, moon@example.com'],
      [$cc_field[0]['address'] . ', ' . $cc_field[1]['address']]);
    // @todo Parse recipients and return list.
    // @todo Test mailbox with display name.

    if (function_exists('idn_to_utf8')) {
      // Single IDN recipient address with decoding.
      $message = new MimeMessage(new MimeHeader([['name' => 'To', 'body' => 'helloWorld@xn--xample-9ua.com']]), 'Bar');
      $this->assertEquals('helloWorld@éxample.com', $message->getTo(TRUE)[0]['address']);
    }
  }

  /**
   * Tests the Cc recipients getter.
   *
   * @covers ::getCc
   */
  public function testGetCc() {
    // Empty recipient.
    $message = new MimeMessage(new MimeHeader([[]]), 'I am a body');
    $cc_field = $message->getCC();
    $this->assertEquals(NULL, $cc_field);

    // Single recipient address.
    $message = new MimeMessage(new MimeHeader([['name' => 'Cc', 'body' => 'sunshine@example.com']]), 'I am a body');
    $cc_field = $message->getCC();
    $this->assertEquals('sunshine@example.com', $cc_field[0]['address']);

    // Multiple recipients.
    // @todo Parse recipients and return list.
    $message = new MimeMessage(new MimeHeader([['name' => 'Cc', 'body' => 'sunshine@example.com, moon@example.com']]), 'I am a body');
    $cc_field = $message->getCC();
    $this->assertEquals(['sunshine@example.com, moon@example.com'],
      [$cc_field[0]['address'] . ', ' . $cc_field[1]['address']]);

    // @todo Also test mailbox with display name.
  }

  /**
   * Tests the 'Received' date getter.
   *
   * @covers ::getReceivedDate
   */
  public function testGetReceivedDate() {
    $message = new MimeMessage(new MimeHeader([
      ['name' => 'Received', 'body' => 'blah; Thu, 29 Jan 2015 15:43:04 +0100'],
    ]), 'I am a body');
    $expected_date = new DateTimePlus('Thu, 29 Jan 2015 15:43:04 +0100');
    $this->assertEquals($expected_date, $message->getReceivedDate());
    $this->assertEmpty($message->getReceivedDate()->getErrors());

    // By RFC2822 time-zone abbreviation is invalid and needs to be removed.
    $message = new MimeMessage(new MimeHeader([
      ['name' => 'Received', 'body' => 'FooBar; Fri, 21 Oct 2016 11:15:25 +0200 (CEST)'],
    ]), 'I am a body');
    $expected_date = new DateTimePlus('Fri, 21 Oct 2016 11:15:25 +0200');
    $this->assertEquals($expected_date, $message->getReceivedDate());
    $this->assertEmpty($message->getReceivedDate()->getErrors());

    $received_string = "by (localhost) via (inmail) with test_fetcher dbvMO4Ox id\n <CAFZOsfMjtXehXPGxbiLjydzCY0gCkdngokeQACWQOw+9W5drqQ@mail.example.com>; Wed, 26 Oct 2016 02:50:11 +1100 (GFT)";
    $message = new MimeMessage(new MimeHeader([
      ['name' => 'Received', 'body' => $received_string],
    ]), 'I am Body');
    $expected_date = new DateTimePlus('Wed, 26 Oct 2016 02:50:11 +1100');
    $this->assertEquals($expected_date, $message->getReceivedDate());
    // It is parsed time zone, everything else must remain untouched.
    $this->assertEquals($received_string, $message->getHeader()->getFieldBody('Received'));
    $this->assertEmpty($message->getReceivedDate()->getErrors());

    // Assert no "Received" field.
    $message = new MimeMessage(new MimeHeader(), 'Body');
    $this->assertEquals(NULL, $message->getReceivedDate());
  }

  /**
   * Tests the message is valid and contains necessary fields.
   *
   * @covers ::validate
   * @covers ::getValidationErrors
   * @covers ::setValidationError
   */
  public function testValidation() {
    // By RFC 5322 (https://tools.ietf.org/html/rfc5322#section-3.6,
    // table on p. 21), the only required MimeHeader fields are From and Date.
    // In addition, the fields can occur only once per message.
    // MimeMessage triggers checking for presence of Date and From fields,
    // as well checking single occurrence of them.
    $message = new MimeMessage(new MimeHeader([
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

    // MimeMessage contains all necessary fields and only one occurrence of
    // each.
    $message = new MimeMessage(new MimeHeader([
      ['name' => 'From', 'body' => 'Foo'],
      ['name' => 'Date', 'body' => 'Fri, 21 Oct 2016 09:55:03 +0200'],
    ]), 'body');
    $this->assertTrue($message->validate());
    // Validation error messages should not exist.
    $this->assertEmpty($message->getValidationErrors());

    // MimeMessage contains all necessary fields but duplicates.
    $message = new MimeMessage(new MimeHeader([
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

  /**
   * Tests the 'Date' header getter.
   *
   * @covers::getDate
   */
  public function testGetDate() {
    $message = new MimeMessage(new MimeHeader([['name' => 'Date', 'body' => 'Thu, 27 Oct 2016 13:29:36 +0200 (UTC)']]), 'body');
    $expected_date = new DateTimePlus('Thu, 27 Oct 2016 13:29:36 +0200');
    $this->assertEquals($expected_date, $message->getDate());
  }

}
