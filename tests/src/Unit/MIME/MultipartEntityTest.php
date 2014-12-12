<?php

/**
 * @file
 * Contains \Drupal\Tests\inmail\Unit\MIME\MultipartEntityTest.
 */

namespace Drupal\Tests\inmail\Unit\MIME;

use Drupal\Core\Logger\LoggerChannel;
use Drupal\inmail\MIME\Entity;
use Drupal\inmail\MIME\Header;
use Drupal\inmail\MIME\Parser;
use Drupal\inmail\MIME\MultipartEntity;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the Parser, Entity and MultipartEntity classes.
 *
 * @coversDefaultClass \Drupal\inmail\MIME\MultipartEntity
 * @group inmail
 */
class MultipartEntityTest extends UnitTestCase {

  /**
   * Multipart example message copied from RFC 2046.
   *
   * @see https://tools.ietf.org/html/rfc2046#page-21
   *
   * @var string
   */
  const MSG_MULTIPART = <<<EOF
From: Nathaniel Borenstein <nsb@bellcore.com>
To: Ned Freed <ned@innosoft.com>
Date: Sun, 21 Mar 1993 23:56:48 -0800 (PST)
Subject: Sample message
MIME-Version: 1.0
Content-type: multipart/mixed; boundary="simple boundary"

This is the preamble.  It is to be ignored, though it
is a handy place for composition agents to include an
explanatory note to non-MIME conformant readers.

--simple boundary

This is implicitly typed plain US-ASCII text.
It does NOT end with a linebreak.
--simple boundary
Content-type: text/plain; charset=us-ascii

This is explicitly typed plain US-ASCII text.
It DOES end with a linebreak.

--simple boundary--

This is the epilogue.  It is also to be ignored.
EOF;

  /**
   * Tests the parser.
   *
   * @covers \Drupal\inmail\MIME\Parser::parse
   */
  public function testParse() {
    // Parse and compare.
    $parsed_message = (new Parser(new LoggerChannel('test')))->parse(static::MSG_MULTIPART);
    $this->assertEquals(static::getMessage(), $parsed_message);
  }

  /**
   * Tests header accessors.
   *
   * @covers \Drupal\inmail\MIME\Entity::getHeader
   */
  public function testGetHeader() {
    // Compare the whole header.
    $this->assertEquals(static::getMessageHeader(), static::getMessage()->getHeader());
    $this->assertEquals(new Header(), static::getFirstPart()->getHeader());
    $this->assertEquals(static::getSecondPartHeader(), static::getSecondPart()->getHeader());
  }

  /**
   * Tests the multipart part accessor.
   *
   * @covers ::getPart
   */
  public function testGetPart() {
    $this->assertEquals(static::getFirstPart(), static::getMessage()->getPart(0));
    $this->assertEquals(static::getSecondPart(), static::getMessage()->getPart(1));
    $this->assertNull(static::getMessage()->getPart(2));
  }

  /**
   * Tests the body accessor.
   *
   * @covers \Drupal\inmail\MIME\Entity::getBody
   */
  public function testGetBody() {
    $this->assertEquals("This is implicitly typed plain US-ASCII text.\nIt does NOT end with a linebreak.", static::getFirstPart()->getBody());
    $this->assertEquals("This is explicitly typed plain US-ASCII text.\nIt DOES end with a linebreak.\n", static::getSecondPart()->getBody());
    $this->assertEquals(static::getBody(), static::getMessage()->getBody());
  }

  /**
   * Tests string serialization.
   *
   * @covers \Drupal\inmail\MIME\Entity::toString
   */
  public function testToString() {
    $this->assertEquals(static::MSG_MULTIPART, static::getMessage()->toString());
  }

  /**
   * Just to make it obvious, test that toString() inverts parse().
   */
  public function testParseToString() {
    $parser = new Parser(new LoggerChannel('test'));

    // Parse and back again.
    $parsed = $parser->parse(static::MSG_MULTIPART);
    $this->assertEquals(static::MSG_MULTIPART, $parsed->toString());

    // To string and back again.
    $string = static::getMessage()->toString();
    $this->assertEquals(static::getMessage(), $parser->parse($string));
  }

  /**
   * Expected parse result of ::MSG_MULTIPART.
   */
  protected static function getMessage() {
    // The multipart message corresponding to the final parse result.
    return new MultipartEntity(
      new Entity(static::getMessageHeader(), static::getBody()),
      [
        static::getFirstPart(),
        static::getSecondPart(),
      ]
    );
  }

  /**
   * Expected parse result of the header of the message (the outer entity).
   */
  protected static function getMessageHeader() {
    return new Header([
      ['name' => 'From', 'body' => 'Nathaniel Borenstein <nsb@bellcore.com>'],
      ['name' => 'To', 'body' => 'Ned Freed <ned@innosoft.com>'],
      ['name' => 'Date', 'body' => 'Sun, 21 Mar 1993 23:56:48 -0800 (PST)'],
      ['name' => 'Subject', 'body' => 'Sample message'],
      ['name' => 'MIME-Version', 'body' => '1.0'],
      ['name' => 'Content-type', 'body' => 'multipart/mixed; boundary="simple boundary"'],
    ]);
  }

  /**
   * Expected parse result of the body of the message.
   */
  protected static function getBody() {
    return 'This is the preamble.  It is to be ignored, though it
is a handy place for composition agents to include an
explanatory note to non-MIME conformant readers.

--simple boundary

This is implicitly typed plain US-ASCII text.
It does NOT end with a linebreak.
--simple boundary
Content-type: text/plain; charset=us-ascii

This is explicitly typed plain US-ASCII text.
It DOES end with a linebreak.

--simple boundary--

This is the epilogue.  It is also to be ignored.';
  }

  /**
   * Expected parse result of the first multipart part.
   */
  protected static function getFirstPart() {
    return new Entity(new Header(), "This is implicitly typed plain US-ASCII text.\nIt does NOT end with a linebreak.");
  }

  /**
   * Expected parse result of the second multipart part.
   */
  protected static function getSecondPart() {
    return new Entity(static::getSecondPartHeader(), "This is explicitly typed plain US-ASCII text.\nIt DOES end with a linebreak.\n");
  }

  /**
   * Expected parse result of the header of the second multipart part.
   */
  protected static function getSecondPartHeader() {
    return new Header([
      ['name' => 'Content-type', 'body' => 'text/plain; charset=us-ascii'],
    ]);
  }

}
