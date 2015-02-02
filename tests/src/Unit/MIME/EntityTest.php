<?php
/**
 * @file
 * Contains \Drupal\Tests\inmail\Unit\MIME\EntityTest.
 */

namespace Drupal\Tests\inmail\Unit\MIME;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\inmail\MIME\Entity;
use Drupal\inmail\MIME\Header;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the MIME Entity class.
 *
 * @coversDefaultClass \Drupal\inmail\MIME\Entity
 *
 * @group inmail
 */
class EntityTest extends UnitTestCase {

  /**
   * Tests the 'Received' date getter.
   *
   * @covers ::getReceivedDate
   */
  public function testGetReceivedDate() {
    $message = new Entity(new Header([
      ['name' => 'Received', 'body' => 'blah; Thu, 29 Jan 2015 15:43:04 +0100'],
    ]), 'I am a body');

    $expected_date = new DateTimePlus('Thu, 29 Jan 2015 15:43:04 +0100');

    $this->assertEquals($expected_date, $message->getReceivedDate());
  }

}
