<?php
/**
 * @file
 * Contains \Drupal\Tests\inmail\Unit\MessageTest.
 */

namespace Drupal\Tests\inmail\Unit;

use Drupal\inmail\Message;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the Message class.
 *
 * @todo Complete MessageTest https://www.drupal.org/node/2381889
 * Methods not test covered:
 * - getHeaders() could be compared to an exact value
 * - isMultipart()
 * - isDSN()
 * - getParts()
 *
 * @coversDefaultClass \Drupal\inmail\Message
 * @group inmail
 */
class MessageTest extends UnitTestCase {

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
