<?php
/**
 * @file
 * Contains \Drupal\inmail\Tests\ProcessorTest.
 */

namespace Drupal\inmail\Tests;

use Drupal\simpletest\KernelTestBase;

/**
 * Tests the behaviour of the MessageProcessor class.
 *
 * @group inmail
 */
class ProcessorTest extends KernelTestBase {

  public static $modules = array('inmail', 'inmail_test', 'dblog');

  protected function setUp() {
    parent::setUp();
    $this->installSchema('dblog', ['watchdog']);
  }

  /**
   * Tests that the processor handles invalid messages by logging.
   */
  public function testMalformedMessage() {
    // Process a malformed message.
    /** @var \Drupal\inmail\MessageProcessorInterface $processor */
    $processor = \Drupal::service('inmail.processor');
    $path = drupal_get_path('module', 'inmail_test') . '/eml/malformed/headerbody.eml';
    $raw = file_get_contents(DRUPAL_ROOT . '/' . $path);
    $processor->process($raw);

    // Check last DbLog message.
    $dblog_statement = \Drupal::database()->select('watchdog', 'w')
      ->orderBy('timestamp', 'DESC')
      ->fields('w', ['message'])
      ->execute();
    $dblog_entry = $dblog_statement->fetchAssoc();
    debug($dblog_entry);
    $this->assertEqual('Unable to process message, parser failed with message "@message"', $dblog_entry['message']);
  }

}
