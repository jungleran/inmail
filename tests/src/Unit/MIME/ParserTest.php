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
 * @group inmail
 */
class ParserTest extends InmailUnitTestBase {

  /**
   * Tests that an exception is thrown when parsing fails.
   *
   * @covers ::parse
   * @dataProvider provideMalformedRaws
   * @expectedException \Drupal\inmail\MIME\ParseException
   */
  public function testParseException($raw) {
    (new Parser(new LoggerChannel('test')))->parse($raw);
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

}
