<?php

namespace Drupal\inmail\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides common helper methods for Inmail web tests.
 *
 * @group inmail
 */
abstract class InmailWebTestBase extends WebTestBase {

  use DelivererTestTrait;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Create a test user and log in.
    $user = $this->drupalCreateUser([
      'access administration pages',
      'administer inmail',
    ]);
    $this->drupalLogin($user);

    // Place local tasks and local actions.
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('local_actions_block');
    $this->drupalPlaceBlock('page_title_block');

    // Set the Inmail processor and parser services.
    $this->processor = \Drupal::service('inmail.processor');
    $this->parser = \Drupal::service('inmail.mime_parser');
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
  protected function getMessageFileContents($filename) {
    $path = drupal_get_path('module', 'inmail_test') . '/eml/' . $filename;
    return file_get_contents(DRUPAL_ROOT . '/' . $path);
  }

  /**
   * Returns the last event with a given machine name.
   *
   * @param string $machine_name
   *   The event machine name.
   * @param bool $id
   *   (optional) Set to TRUE if the last event ID is needed.
   *
   * @return \Drupal\past\PastEventInterface|null
   *   The past event or null if not found.
   */
  protected function getLastEventByMachinename($machine_name, $id = FALSE) {
    $event = db_query_range('SELECT event_id FROM {past_event} WHERE machine_name = :machine_name ORDER BY event_id DESC', 0, 1, [':machine_name' => $machine_name])->fetchField();
    if ($event && !$id) {
      $event = \Drupal::entityTypeManager()->getStorage('past_event')->load($event);
    }
    return $event;
  }

}
