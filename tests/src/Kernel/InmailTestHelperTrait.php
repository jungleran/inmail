<?php

namespace Drupal\Tests\inmail\Kernel;

/**
 * Provides common helper methods for Inmail testing.
 */
trait InmailTestHelperTrait {

  /**
   * Returns the content of a test message.
   *
   * @param string $filename
   *   The name of the file.
   * @param string $module
   *   (optional) The module name. Defaults to "inmail_test".
   *
   * @return string
   *   The contents of the file.
   */
  protected function getMessageFileContents($filename, $module = 'inmail_test') {
    $path = drupal_get_path('module', $module) . '/eml/' . $filename;
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
    $event = \Drupal::database()->queryRange('SELECT event_id FROM {past_event} WHERE machine_name = :machine_name ORDER BY event_id DESC', 0, 1, [':machine_name' => $machine_name])->fetchField();
    if ($event && !$id) {
      $event = \Drupal::entityTypeManager()->getStorage('past_event')->load($event);
    }
    return $event;
  }

}
