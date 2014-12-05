<?php
/**
 * @file
 * Contains \Drupal\inmail\Tests\InmailWebTest.
 */

namespace Drupal\inmail\Tests;

use Drupal\inmail\Entity\AnalyzerConfig;
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

    // Check validation.
    $this->drupalPostForm(NULL, ['return_path' => 'not an address'], 'Save configuration');
    $this->assertText('This is not a valid email address.');

    $this->drupalPostForm(NULL, ['return_path' => 'not+allowed@example.com'], 'Save configuration');
    $this->assertText('The address may not contain a + character.');

    $this->drupalPostForm(NULL, ['return_path' => 'bounces@example.com'], 'Save configuration');
    $this->assertText('The configuration options have been saved.');

    // Check Analyzer list.
    $this->clickLink('Message analyzers');
    $this->assertText('Standard DSN Analyzer');
    $this->assertText('Standard bounce analyzer');
    $this->assertText('Standard DSN Reason Analyzer');
    $this->assertText('Bounce reason message');
    $this->assertText('VERP Analyzer');
    $this->assertText('VERP address verification');

    $this->assertNoLink('Enable');
    $this->clickLink('Disable');
    $this->clickLink('Enable');

    $this->assertFieldByXPath('//table[@id="edit-entities"]/tbody/tr[1]/td/text()', 'VERP Analyzer');
    $this->assertFieldByXPath('//table[@id="edit-entities"]/tbody/tr[2]/td/text()', 'Standard DSN Analyzer');
    $this->assertFieldByXPath('//table[@id="edit-entities"]/tbody/tr[3]/td/text()', 'Standard DSN Reason Analyzer');

    AnalyzerConfig::create(array(
      'id' => 'unicorn',
      'plugin_id' => 'unicorn',
      'label' => 'Unicorn',
    ))->save();
    $this->drupalGet('admin/config/system/inmail/analyzers');
    $this->assertText('Unicorn');
    $this->assertText('Plugin missing');

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
