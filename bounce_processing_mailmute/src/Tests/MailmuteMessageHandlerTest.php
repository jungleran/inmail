<?php
/**
 * @file
 * Contains \Drupal\bounce_processing_mailmute\Tests\MailmuteMessageHandlerTest.
 */

namespace Drupal\bounce_processing_mailmute\Tests;

use Drupal\simpletest\KernelTestBase;
use Drupal\user\Entity\User;

/**
 * Tests the Mailmute message handler.
 *
 * @group bounce_processing
 */
class MailmuteMessageHandlerTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'bounce_processing_mailmute',
    'bounce_processing_test',
    'bounce_processing',
    'mailmute',
    'user',
    'field',
    'system',
  ];

  /**
   * A user matching the recipient in the test messages.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installSchema('system', ['sequences']);
    $this->installSchema('user', ['users_data']);
    $this->installEntitySchema('user');
    $this->installConfig(['mailmute', 'system']);
    \Drupal::config('system.settings')->set('site.mail', 'bounces@example.com');
  }

  /**
   * Classify messages and test that the send state is transitioned correctly.
   */
  public function testClassifyAndTriggerSendStateTransition() {
    /** @var \Drupal\bounce_processing\MessageProcessorInterface $processor */
    $processor = \Drupal::service('bounce.processor');

    $cases = array(
      // Normal message should not trigger mute.
      'normal.eml' => 'send',
      // "Mailbox full" bounce should not trigger mute.
      'full.eml' => 'send',
      // "No such user" bounce should trigger mute.
      'nouser.eml' => 'bounce_invalid_address',
      // "Access denied" bounce should trigger mute.
      'accessdenied.eml' => 'bounce_invalid_address',
    );

    foreach ($cases as $filename => $expected) {
      $this->resetUser();
      $raw = $this->getMessageFileContents($filename);

      // Let magic happen.
      // @todo This whole test doesn't make any sense unless SimpleDSNClassifier
      // starts trying to find a recipient address.
      $processor->process($raw);

      // Reload user.
      $this->user = User::load($this->user->id());

      // Check the outcome.
      $this->assertEqual($this
        ->user
        ->field_sendstate
        ->value, $expected);
    }
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
    $path = drupal_get_path('module', 'bounce_processing') . '/tests/modules/bounce_processing_test/eml/' . $filename;
    return file_get_contents(DRUPAL_ROOT . '/' . $path);
  }

  /**
   * Creates a new test user, deleting the previous one if it exists.
   */
  public function resetUser() {
    // Delete the user if it exists.
    if (isset($this->user)) {
      $this->user->delete();
    }
    $this->user = User::create(array(
      'name' => 'user',
      'mail' => 'user@example.org',
    ));
    $this->user->save();
  }

}
