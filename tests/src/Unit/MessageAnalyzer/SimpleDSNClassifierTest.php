<?php
/**
 * @file
 * Contains \Drupal\Tests\bounce_processing\Unit\MessageAnalyzer\SimpleDSNClassifierTest.
 */

namespace Drupal\Tests\bounce_processing\Unit\MessageAnalyzer;

use Drupal\bounce_processing\AnalyzerResult;
use Drupal\bounce_processing\Message;
use Drupal\bounce_processing\MessageAnalyzer\SimpleDSNClassifier;
use Drupal\Tests\bounce_processing\Unit\BounceProcessingUnitTestBase;

/**
 * Unit tests the simple DSN classifier.
 *
 * @coversDefaultClass \Drupal\bounce_processing\MessageAnalyzer\SimpleDSNClassifier
 * @group bounce_processing
 */
class SimpleDSNClassifierTest extends BounceProcessingUnitTestBase {

  /**
   * Tests the classify method.
   *
   * @covers ::classify
   * @dataProvider provideExpectedResults
   */
  public function testClassify($filename, $expected_code, $expected_recipient) {
    $message = Message::parse($this->getRaw($filename));

    // Run the classifier.
    $classifier = new SimpleDSNClassifier();
    $result = new AnalyzerResult();
    $classifier->classify($message, $result);

    // Test the reported code.
    if (isset($expected_code)) {
      $this->assertEquals($expected_code, $result->getBounceStatusCode()->getCode());
    }
    else {
      $this->assertNull($result->getBounceStatusCode());
    }

    // Test the reported target recipient.
    if (isset($expected_recipient)) {
      $this->assertEquals($expected_recipient, $result->getBounceRecipient());
    }
    else {
      $this->assertNull($result->getBounceRecipient());
    }
  }

  /**
   * Provides expected analysis results for test message files.
   */
  public function provideExpectedResults() {
    return [
      ['accessdenied.eml', '5.0.0', 'user@example.org'],
      ['full.eml', '4.2.2', 'user@example.org'],
      ['normal.eml', NULL, NULL],
      ['nouser.eml', '5.1.1', 'user@example.org'],
    ];
  }

}
