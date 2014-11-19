<?php
/**
 * @file
 * Contains \Drupal\Tests\inmail\Unit\MessageTest.
 */

namespace Drupal\Tests\inmail\Unit;

use Drupal\inmail\Message;
use Drupal\Tests\UnitTestCase;

/**
 * Class MessageTest
 *
 * @coversDefaultClass \Drupal\inmail\Message
 * @group inmail
 */
class MessageTest extends UnitTestCase {

  /**
   * Tests some Message methods.
   *
   * @covers ::parse
   * @covers ::getHeaders
   * @covers ::getHeader
   * @covers ::getBody
   * @covers ::getRaw
   *
   * @todo This is not a unit test! Split it up.
   */
  public function testEverything() {
    $raw = <<<"EOF"
X-Single-Line-Header:  This should be trimmed
X-Multi-Line-Header: This suit is black
 not!

I'm a message body.

I'm the same body.
EOF;

    $message = Message::parse($raw);

    $this->assertCount(2, $message->getHeaders());
    $this->assertEquals('This should be trimmed', $message->getHeader('x-single-line-header'));
    $this->assertEquals("This suit is black\n not!", $message->getHeader('x-multi-line-header'));
    $this->assertEquals("I'm a message body.\n\nI'm the same body.", $message->getBody());
    $this->assertEquals($raw, $message->getRaw());
    $this->assertFalse($message->isMultipart());
    $this->assertNull($message->getParts());
  }

  /**
   * Test address parsing.
   *
   * @covers ::parseAddress
   * @dataProvider provideAddresses
   */
  public function testParseAddress($field, $expected) {
    $this->assertEquals($expected, Message::parseAddress($field));
  }

  /**
   * Provide email address fields to test parseAddress with.
   */
  public static function provideAddresses() {
    return [
      [' admin@example.com ', ['admin@example.com']],
      ['a@b.c, d.e@f.g.h', ['a@b.c', 'd.e@f.g.h']],
      ['Admin <admin@example.com>', ['admin@example.com']],
      ['Admin <admin@example.com>, User <user.name@users.example.com>', ['admin@example.com', 'user.name@users.example.com']],
    ];
  }

}
