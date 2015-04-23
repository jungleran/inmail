<?php
/**
 * @file
 * Contains \Drupal\inmail\Tests\AnalyzerTest.
 */

namespace Drupal\inmail\Tests;

use Drupal\inmail\BounceAnalyzerResult;
use Drupal\inmail\Entity\DelivererConfig;
use Drupal\inmail\Entity\HandlerConfig;
use Drupal\inmail_test\Plugin\inmail\Handler\ResultKeeperHandler;
use Drupal\simpletest\KernelTestBase;

/**
 * Tests analyzers.
 *
 * @group inmail
 */
class AnalyzerTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('inmail', 'inmail_test');

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(['inmail']);
    \Drupal::configFactory()->getEditable('inmail.settings')
      ->set('return_path', 'bounces@example.com')
      ->save();
  }

  /**
   * Tests an entire processor pass from the aspect of order of analyzers.
   */
  public function testEffectivePriority() {
    // This message is designed to challenge the priority in which analyzers are
    // invoked: if priority is not working correctly, StandardDSNAnalyzer comes
    // before VerpAnalyzer (because of alphabetical sorting?) and sets the
    // recipient property from the Final-Recipient part of the DSN report.
    // With correct priorities, VerpAnalyzer will come first and set the
    // property using the more reliable VERP address.
    $raw = <<<EOF
To: bounces+verp-parsed=example.org@example.com
Content-Type: multipart/report; report-type=delivery-status; boundary="BOUNDARY"

This part is ignored.

--BOUNDARY

Message bounced because of reasons.

--BOUNDARY
Content-Type: message/delivery-status

Status: 4.1.1
Final-Recipient: rfc822; dsn-parsed@example.org

--BOUNDARY
Content-Type: message/rfc822

Subject: Original message.

--BOUNDARY--
EOF;

    /** @var \Drupal\inmail\MessageProcessorInterface $processor */
    $processor = \Drupal::service('inmail.processor');

    HandlerConfig::create(array('id' => 'result_keeper', 'plugin' => 'result_keeper'))->save();
    $processor->process($raw, DelivererConfig::create(array('id' => 'test')));

    $processor_result = ResultKeeperHandler::$result;
    /** @var \Drupal\inmail\BounceAnalyzerResult $result */
    $result = $processor_result->getAnalyzerResult(BounceAnalyzerResult::TOPIC);

    $this->assertEqual($result->getRecipient(), 'verp-parsed@example.org');
  }

}
