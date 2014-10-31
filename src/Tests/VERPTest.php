<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\Tests\VERPTest.
 */

namespace Drupal\bounce_processing\Tests;

use Drupal\bounce_processing_test\MessageHandler\ResultKeeperHandler;
use Drupal\Core\Language\LanguageInterface;
use Drupal\simpletest\KernelTestBase;

/**
 * Tests the VERP mechanism.
 *
 * @group bounce_processing
 */
class VERPTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('bounce_processing', 'bounce_processing_test', 'system');

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installConfig(['bounce_processing', 'system']);
    \Drupal::config('system.site')->set('mail', 'bounces@example.com');
  }

  /**
   * Test the VERP mechanism.
   */
  public function testVERP() {
    // Send a message and check the modified Return-Path.
    $recipient = 'user@example.org';
    $expected_returnpath = 'bounces+user=example.org@example.com';

    $message = \Drupal::service('plugin.manager.mail')->mail('bounce_processing_test', 'VERP', $recipient, LanguageInterface::LANGCODE_DEFAULT);
    $this->assertEqual($message['headers']['Return-Path'], $expected_returnpath);

    // Process a bounce message with a VERP-y 'To' header, check the parsing.
    $path = drupal_get_path('module', 'bounce_processing') . '/tests/modules/bounce_processing_test/eml/full.eml';
    $raw = file_get_contents(DRUPAL_ROOT . '/' . $path);
    $result_keeper = new ResultKeeperHandler();
    $processor = \Drupal::service('bounce.processor');
    $processor->removeAnalyzer('bounce.analyzer.simple_dsn_classifier');
    $processor->addHandler($result_keeper, 'bounce.handler.keeper');
    $processor->process($raw);

    $parsed_recipient = $result_keeper->result->getBounceRecipient();
    $this->assertEqual($parsed_recipient, $recipient);
  }

}
