<?php
/**
 * @file
 * Contains \Drupal\inmail\Tests\VERPTest.
 */

namespace Drupal\inmail\Tests;

use Drupal\inmail\BounceAnalyzerResult;
use Drupal\inmail\Entity\AnalyzerConfig;
use Drupal\inmail\Entity\DelivererConfig;
use Drupal\inmail\Entity\HandlerConfig;
use Drupal\inmail_test\Plugin\inmail\Handler\ResultKeeperHandler;
use Drupal\Core\Language\LanguageInterface;
use Drupal\simpletest\KernelTestBase;

/**
 * Tests the VERP mechanism.
 *
 * @group inmail
 */
class VERPTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('inmail', 'inmail_test', 'system');

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installConfig(['inmail', 'system']);
    \Drupal::config('system.site')->set('mail', 'bounces@example.com');
    \Drupal::config('system.mail')->set('interface', ['default' => 'test_mail_collector']);
  }

  /**
   * Test the VERP mechanism.
   */
  public function testVERP() {
    // Send a message and check the modified Return-Path.
    $recipient = 'user@example.org';
    $expected_returnpath = 'bounces+user=example.org@example.com';

    $message = \Drupal::service('plugin.manager.mail')->mail('inmail_test', 'VERP', $recipient, LanguageInterface::LANGCODE_DEFAULT);
    $this->assertEqual($message['headers']['Return-Path'], $expected_returnpath);

    // Enable ResultKeeperHandler.
    HandlerConfig::create(array('id' => 'result_keeper', 'plugin' => 'result_keeper'))->save();
    // Disable the StandardDSNAnalyzer because it also reports the correct
    // recipient address.
    AnalyzerConfig::load('dsn')->disable()->save();

    // Process a bounce message with a VERP-y 'To' header, check the parsing.
    $path = drupal_get_path('module', 'inmail_test') . '/eml/full.eml';
    $raw = file_get_contents(DRUPAL_ROOT . '/' . $path);
    /** @var \Drupal\inmail\MessageProcessorInterface $processor */
    $processor = \Drupal::service('inmail.processor');
    $processor->process($raw, DelivererConfig::create(array('id' => 'test')));

    /** @var \Drupal\inmail\BounceAnalyzerResult $result */
    $result = ResultKeeperHandler::$result->getAnalyzerResult(BounceAnalyzerResult::TOPIC);
    $parsed_recipient = $result->getRecipient();
    $this->assertEqual($parsed_recipient, $recipient);
  }

}
