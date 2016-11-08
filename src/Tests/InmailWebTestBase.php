<?php

namespace Drupal\inmail\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\Tests\inmail\Kernel\InmailTestHelperTrait;

/**
 * Provides common helper methods for Inmail web tests.
 *
 * @group inmail
 * @requires module past_db
 */
abstract class InmailWebTestBase extends WebTestBase {

  use DelivererTestTrait, InmailTestHelperTrait;

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

    // Set the Inmail processor and parser services.
    $this->processor = \Drupal::service('inmail.processor');
    $this->parser = \Drupal::service('inmail.mime_parser');
  }

}
