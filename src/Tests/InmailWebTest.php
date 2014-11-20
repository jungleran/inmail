<?php
/**
 * @file
 * Contains \Drupal\inmail\Tests\InmailWebTest.
 */

namespace Drupal\inmail\Tests;

use Drupal\inmail\Entity\HandlerConfig;
use Drupal\simpletest\WebTestBase;

/**
 * Tests the UI of Inmail.
 *
 * @group inmail
 */
class InmailWebTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('inmail');

  /**
   * Tests the admin UI.
   */
  public function testAdminUI() {
    // Create a test user and log in.
    $user = $this->drupalCreateUser(array(
      'access administration pages',
      'administer inmail',
    ));
    $this->drupalLogin($user);

    // Check the form.
    $this->drupalGet('admin/config');
    $this->clickLink('Inmail');
    $this->assertField('return_path');
    $this->assertField('verp');

    // Check validation.
    $this->drupalPostForm(NULL, ['return_path' => 'not an address'], 'Save configuration');
    $this->assertText('This is not a valid email address.');

    $this->drupalPostForm(NULL, ['return_path' => 'not+allowed@example.com'], 'Save configuration');
    $this->assertText('The address may not contain a + character.');

    $this->drupalPostForm(NULL, ['return_path' => 'bounces@example.com'], 'Save configuration');
    $this->assertText('The configuration options have been saved.');

    // Check Handler list and fallback plugin.
    $this->clickLink('Message handlers');
    $this->assertText('Forward unclassified bounces');
    $this->assertNoLink('Enable');
    $this->clickLink('Disable');
    $this->assertNoLink('Disable');
    $this->assertLink('Enable');

    HandlerConfig::create(array(
      'id' => 'unicorn',
      'plugin_id' => 'unicorn',
      'label' => 'Unicorn',
    ))->save();
    $this->drupalGet('admin/config/system/inmail/handlers');
    $this->assertText('Unicorn');
    $this->assertText('Plugin missing');
  }

}
