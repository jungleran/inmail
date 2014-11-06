<?php
/**
 * @file
 * Contains \Drupal\Tests\inmail\Unit\MessageAnalyzer\StandardDSNAnalyzerTest.
 */

namespace Drupal\Tests\inmail\Unit\MessageAnalyzer;

use Drupal\inmail\AnalyzerResult;
use Drupal\inmail\Message;
use Drupal\inmail\MessageAnalyzer\StandardDSNAnalyzer;
use Drupal\Tests\inmail\Unit\InmailUnitTestBase;

/**
 * Unit tests the DSN bounce message Analyzer.
 *
 * @coversDefaultClass \Drupal\inmail\MessageAnalyzer\StandardDSNAnalyzer
 * @group inmail
 */
class StandardDSNAnalyzerTest extends InmailUnitTestBase {

  /**
   * Tests the analyze method.
   *
   * @covers ::analyze
   * @dataProvider provideExpectedResults
   */
  public function testAnalyze($filename, $expected_code, $expected_recipient) {
    $message = Message::parse($this->getRaw($filename));

    // Run the analyzer.
    $analyzer = new StandardDSNAnalyzer();
    $result = new AnalyzerResult();
    $analyzer->analyze($message, $result);

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
