<?php
/**
 * @file
 * Contains \Drupal\Tests\inmail\Unit\MIME\ParserTest.
 */

namespace Drupal\Tests\inmail\Unit\MIME;

use Drupal\Core\Logger\LoggerChannel;
use Drupal\inmail\MIME\Parser;
use Drupal\Tests\inmail\Unit\InmailUnitTestBase;

/**
 * Tests the MIME Parser class.
 *
 * @coversDefaultClass \Drupal\inmail\MIME\Parser
 *
 * @group inmail
 */
class ParserTest extends InmailUnitTestBase {

  /**
   * Tests that an exception is thrown when parsing fails.
   *
   * @covers ::parseMessage
   *
   * @dataProvider provideMalformedRaws
   *
   * @expectedException \Drupal\inmail\MIME\ParseException
   */
  public function testParseException($raw) {
    (new Parser(new LoggerChannel('test')))->parseMessage($raw);
  }

  /**
   * Provides invalid entities that should cause the parser to fail.
   */
  public function provideMalformedRaws() {
    return [
      [$this->getRaw('malformed/headerbody.eml')],
      // @todo Cover more cases of invalid messages.
    ];
  }

  /**
   * Test address parsing.
   *
   * @covers ::parseAddress
   *
   * @dataProvider provideAddresses
   */
  public function testParseAddress($field, $expected) {
    $this->assertEquals($expected, Parser::parseAddress($field));
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
