<?php
/**
 * @file
 * Contains \Drupal\inmail\Tests\ModeratorForwardTest.
 */

namespace Drupal\inmail\Tests;

use Drupal\inmail\Entity\Handler;
use Drupal\simpletest\KernelTestBase;

/**
 * Tests the Moderator Forward handler plugin.
 *
 * @group inmail
 */
class ModeratorForwardTest extends KernelTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = array('inmail', 'system');

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installConfig(array('inmail'));
    $this->installEntitySchema('inmail_handler');
    \Drupal::config('system.mail')
      ->set('interface.default', 'test_mail_collector')
      ->save();
    \Drupal::config('system.site')
      ->set('mail', 'bounces@example.com')
      ->save();
  }

  /**
   * Tests the rules for when forwarding should be done.
   */
  public function testModeratorForwardRules() {
    $processor = \Drupal::service('inmail.processor');
    $bounce = $this->getMessageFileContents('nouser.eml');
    $regular = $this->getMessageFileContents('normal.eml');

    // Do not handle if message is bounce.
    $processor->process($bounce);
    $this->assertMailCount(0);

    // Do not handle if moderator address is unset.
    /** @var \Drupal\inmail\Entity\Handler $handler_config */
    $handler_config = Handler::load('moderator_forward');
    $this->assertEqual($handler_config->getConfiguration()['moderator'], '');
    $processor->process($regular);
    $this->assertMailCount(0);

    // Do not handle, and log an error, if moderator address is same as intended
    // recipient.
    $handler_config->setConfiguration(array('moderator' => 'user@example.org'))->save();
    // Forge a mail where we recognize recipient but not status.
    $bounce_no_status = str_replace('Status:', 'Foo:', $bounce);
    $processor->process($bounce_no_status);
    $this->assertMailCount(0);
    // @todo Read log?

    // Do not handle, and log an error, if the custom X header is set.
    $handler_config->setConfiguration(array('moderator' => 'moderator@example.com'))->save();
    $regular_x = "X-Inmail-Forwarded: ModeratorForwardTest\n" . $regular;
    $processor->process($regular_x);
    $this->assertMailCount(0);
    // @todo Read log?

    // Forward non-bounces if conditions are right.
    $processor->process($regular);
    $this->assertMailCount(1);
  }

  /**
   * Tests the forwarded message.
   */
  public function testModeratorForwardMessage() {
    $this->fail('@todo');
  }

  /**
   * Returns the content of a test message.
   *
   * @param string $filename
   *   The name of the file.
   *
   * @return string
   *   The contents of the file.
   */
  public function getMessageFileContents($filename) {
    $path = drupal_get_path('module', 'inmail') . '/tests/modules/inmail_test/eml/' . $filename;
    return file_get_contents(DRUPAL_ROOT . '/' . $path);
  }

  /**
   * Counts the number of sent mail and compares to an expected value.
   */
  protected function assertMailCount($expected, $message = '', $group = 'Other') {
    $messages = \Drupal::state()->get('system.test_mail_collector');
    $this->assertEqual(count($messages), $expected, $message, $group);
  }

}
