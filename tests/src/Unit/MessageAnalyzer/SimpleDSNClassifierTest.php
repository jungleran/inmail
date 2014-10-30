<?php
/**
 * @file
 * Contains \Drupal\Tests\bounce_processing\Unit\MessageAnalyzer\SimpleDSNClassifierTest.
 */

namespace Drupal\Tests\bounce_processing\Unit\MessageAnalyzer;

use Drupal\bounce_processing\AnalyzerResult;
use Drupal\bounce_processing\Message;
use Drupal\bounce_processing\MessageAnalyzer\SimpleDSNClassifier;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests the simple DSN classifier.
 *
 * @coversClass \Drupal\bounce_processing\MessageAnalyzer\SimpleDSNClassifier
 * @group bounce_processing
 */
class SimpleDSNClassifierTest extends UnitTestCase {

  /**
   * Tests the classify method.
   *
   * @covers ::classify()
   */
  public function testClassify() {
    $cases = array(
      'normal.eml' => NULL,
      'full.eml' => '4.2.2',
      'nouser.eml' => '5.1.1',
      'accessdenied.eml' => '5.0.0',
      // @todo Some more test messages needed.
    );

    foreach ($cases as $filename => $expected) {
      $message = $this->getMessage($filename);

      $classifier = new SimpleDSNClassifier();
      $result = new AnalyzerResult();
      $classifier->classify($message, $result);

      if (isset($expected)) {
        $this->assertEquals($expected, $result->getBounceStatusCode()->getCode());
      }
      else {
        $this->assertNull($result->getBounceStatusCode());
      }

      // @todo Test recipient
    }
  }

  /**
   * Returns a message object for the given file.
   *
   * @param string $filename
   *   The name of the file.
   *
   * @return \Drupal\bounce_processing\Message
   *   A message object representing the message in the file.
   */
  public function getMessage($filename) {
    $path = __DIR__ . '/../../../modules/bounce_processing_test/eml/' . $filename;
    return Message::parse(file_get_contents($path));
  }

}
