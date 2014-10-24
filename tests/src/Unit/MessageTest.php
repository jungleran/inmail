<?php
/**
 * @file
 * Contains \Drupal\Tests\bounce_processing\Unit\MessageTest.
 */

namespace Drupal\Tests\bounce_processing\Unit;

use Drupal\bounce_processing\Message;
use Drupal\Tests\UnitTestCase;

/**
 * Class MessageTest
 *
 * @coversDefaultClass \Drupal\bounce_processing\Message
 * @group bounce_processing
 */
class MessageTest extends UnitTestCase {

  /**
   * Tests some Message methods.
   *
   * @covers ::parse()
   * @covers ::getHeaders()
   * @covers ::getHeader()
   * @covers ::getBody()
   * @covers ::getRaw()
   *
   * @todo This is not a unit test! Split it up.
   */
  public function testEverything() {
    $raw = <<<"EOF"
Single-line-header:  This should be trimmed
Multi-line-header: This suit is black
 not!

I'm a message body.

I'm the same body.
EOF;

    $message = Message::parse($raw);

    $this->assertCount(2, $message->getHeaders());
    $this->assertEquals('This should be trimmed', $message->getHeader('single-line-header'));
    $this->assertEquals("This suit is black\n not!", $message->getHeader('multi-line-header'));
    $this->assertEquals("I'm a message body.\n\nI'm the same body.", $message->getBody());
    $this->assertEquals($raw, $message->getRaw());
  }

}
