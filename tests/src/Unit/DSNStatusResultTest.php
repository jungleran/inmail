<?php
/**
 * @file
 * Contains \Drupal\Tests\inmail\Unit\DSNStatusResultTest.
 */

namespace Drupal\Tests\inmail\Unit;

use Drupal\inmail\DSNStatusResult;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests the DSN status result class.
 *
 * @coversDefaultClass \Drupal\inmail\DSNStatusResult
 * @group inmail
 */
class DSNStatusResultTest extends UnitTestCase {

  /**
   * Tests the constructor for invalid codes.
   *
   * @covers ::__construct
   * @expectedException \InvalidArgumentException
   * @dataProvider provideInvalidCodes
   */
  public function testConstructInvalid($class, $subject, $detail) {
    new DSNStatusResult($class, $subject, $detail);
  }

  /**
   * Tests the parse method for valid codes.
   *
   * @covers ::parse
   * @dataProvider provideCodes
   */
  public function testParse($class, $subject, $detail) {
    DSNStatusResult::parse("$class.$subject.$detail");
  }

  /**
   * Tests the parse method for invalid codes.
   *
   * @covers ::parse
   * @expectedException \InvalidArgumentException
   * @dataProvider provideInvalidCodes
   */
  public function testParseInvalid($class, $subject, $detail) {
    DSNStatusResult::parse("$class.$subject.$detail");
  }

  /**
   * Tests the getCode method.
   *
   * @covers ::getCode
   * @dataProvider provideCodes
   */
  public function testGetCode($class, $subject, $detail) {
    $status = new DSNStatusResult($class, $subject, $detail);
    $this->assertEquals("$class.$subject.$detail", $status->getCode());
  }

  /**
   * Tests the isSuccess method.
   *
   * @covers ::isSuccess
   * @dataProvider provideCodes
   */
  public function testIsSuccess($class, $subject, $detail) {
    $status = new DSNStatusResult($class, $subject, $detail);
    $this->assertEquals($class == 2, $status->isSuccess());
  }

  /**
   * Tests the isPermanentFailure method.
   *
   * @covers ::isPermanentFailure
   * @dataProvider provideCodes
   */
  public function testIsPermanentFailure($class, $subject, $detail) {
    $status = new DSNStatusResult($class, $subject, $detail);
    $this->assertEquals($class == 5, $status->isPermanentFailure());
  }

  /**
   * Tests the isTransientFailure method.
   *
   * @covers ::isTransientFailure
   * @dataProvider provideCodes
   */
  public function testIsTransientFailure($class, $subject, $detail) {
    $status = new DSNStatusResult($class, $subject, $detail);
    $this->assertEquals($class == 4, $status->isTransientFailure());
  }

  /**
   * Tests the getLabel, getClassLabel and getDetailLabel methods.
   *
   * @covers ::getLabel
   * @covers ::getClassLabel
   * @covers ::getDetailLabel
   */
  public function getLabel($class, $subject, $detail) {
    $status = new DSNStatusResult($class, $subject, $detail);
    $this->assertTrue(strlen($status->getClassLabel()) > 0);
    $this->assertTrue(strlen($status->getDetailLabel()) > 0);
    $this->assertEquals($status->getClassLabel() . ': ' . $status->getDetailLabel(), $status->getLabel());
  }

  /**
   * Tests the getRecipient and setRecipient methods.
   *
   * @covers ::setRecipient
   * @covers ::getRecipient
   * @dataProvider provideCodes
   */
  public function testSetGetRecipient($class, $subject, $detail) {
    $recipient = $this->randomMachineName();
    $status = new DSNStatusResult($class, $subject, $detail);
    $this->assertNull($status->getRecipient());
    $status->setRecipient($recipient);
    $this->assertEquals($recipient, $status->getRecipient());
  }

  /**
   * Provides valid DSN status codes.
   *
   * @return array
   *   An array where each element is a three-element array of integers and
   *   represents a status code.
   */
  public function provideCodes() {
    $max_detail_per_subject = [0, 8, 4, 5, 7, 5, 5, 7];
    $codes = [];
    foreach ([2, 4, 5] as $class) {
      foreach (range(0, 7) as $subject) {
        foreach (range(0, $max_detail_per_subject[$subject]) as $detail) {
          $codes[] = [$class, $subject, $detail];
        }
      }
    }
    return $codes;
  }

  /**
   * Provides some invalid DSN status codes.
   *
   * @return array
   *   An array where each element is a three-element array of integers.
   */
  public function provideInvalidCodes() {
    return [
      // Invalid class part.
      [1, 0, 0],
      [3, 0, 0],
      [6, 0, 0],
      // Invalid subject part.
      [4, 8, 0],
      // Invalid detail part; 1 more than the greatest valid for each subject.
      [4, 0, 1],
      [4, 1, 9],
      [4, 2, 5],
      [4, 3, 6],
      [4, 4, 8],
      [4, 5, 6],
      [4, 6, 6],
      [4, 7, 8],
    ];
  }

}
