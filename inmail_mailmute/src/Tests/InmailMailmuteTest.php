<?php
/**
 * @file
 * Contains \Drupal\inmail_mailmute\Tests\InmailMailmuteTest.
 */

namespace Drupal\inmail_mailmute\Tests;

use Drupal\Component\Utility\String;
use Drupal\inmail\AnalyzerResult;
use Drupal\inmail\DSNStatusResult;
use Drupal\inmail\Message;
use Drupal\simpletest\KernelTestBase;
use Drupal\user\Entity\User;

/**
 * Tests the Mailmute message handler.
 *
 * @group inmail
 */
class InmailMailmuteTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'inmail_mailmute',
    'inmail_test',
    'inmail',
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
  }

  /**
   * Process messages and test that the send state is transitioned correctly.
   */
  public function testProcessAndTriggerSendStateTransition() {
    /** @var \Drupal\inmail\MessageProcessorInterface $processor */
    $processor = \Drupal::service('inmail.processor');

    $cases = array(
      // Normal message should not trigger mute.
      'normal.eml' => 'send',
      // "Mailbox full" bounce should not trigger mute.
      'full.eml' => 'send',
      // "No such user" bounce should trigger mute.
      'nouser.eml' => 'inmail_invalid_address',
      // "Access denied" bounce should trigger mute.
      'accessdenied.eml' => 'inmail_invalid_address',
    );

    foreach ($cases as $filename => $expected) {
      $this->resetUser();
      $raw = $this->getMessageFileContents($filename);

      // Let magic happen.
      $processor->process($raw);

      // Reload user.
      $this->user = User::load($this->user->id());

      // Check the outcome.
      $this->assertEqual($this->user->field_sendstate->value, $expected);
    }
  }

  /**
   * Test the "Persistent send" state.
   */
  public function testPersistentSendstate() {
    /** @var \Drupal\mailmute\SendStateManagerInterface $sendstate_manager */
    $sendstate_manager = \Drupal::service('plugin.manager.sendstate');
    $this->resetUser();

    // Some bounce result statuses to test.
    /** @var \Drupal\inmail\DSNStatusResult[] $statuses */
    $statuses = array(
      // Not a bounce.
      new DSNStatusResult(2, 0, 0),
      // Soft bounce (temporarily unavailable).
      new DSNStatusResult(4, 0, 0),
      // Hard bounce (unexisting addres etc).
      new DSNStatusResult(5, 0, 0),
    );

    foreach ($statuses as $status) {
      // Set the user's state to Persistent send.
      $sendstate_manager->setState($this->user->getEmail(), 'persistent_send');

      // Invoke the handler.
      $result = new AnalyzerResult();
      $result->setBounceStatusCode($status);
      \Drupal::service('inmail.handler.mailmute')->invoke(new Message(), $result);

      // Check that the state did not change.
      $new_state = $sendstate_manager->getState($this->user->getEmail());
      $message = String::format('Status %status results in state %state', array('%status' => $status->getCode(), '%state' => $new_state->getPluginId()));
      $this->assertEqual($new_state->getPluginId(), 'persistent_send', $message);
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
    $path = drupal_get_path('module', 'inmail') . '/tests/modules/inmail_test/eml/' . $filename;
    return file_get_contents(DRUPAL_ROOT . '/' . $path);
  }

  /**
   * Creates a new test user, deleting the previous one if it exists.
   *
   * The email address of the test user corresponds with the contents of the
   * test message files.
   */
  public function resetUser() {
    // Delete the user if it exists.
    if (isset($this->user)) {
      $this->user->delete();
    }
    // Create new user.
    $this->user = User::create(array(
      'name' => 'user',
      'mail' => 'user@example.org',
    ));
    $this->user->save();
  }

}
