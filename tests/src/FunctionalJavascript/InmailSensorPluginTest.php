<?php

namespace Drupal\Tests\inmail\FunctionalJavascript;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\inmail\Traits\DelivererTestTrait;

/**
 * Tests Inmail sensor plugins.
 *
 * @group inmail
 */
class InmailSensorPluginTest extends WebDriverTestBase {

  use DelivererTestTrait, StringTranslationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'inmail',
    'inmail_test',
    'monitoring',
    'node',
  ];

  /**
   * Tests incoming mails sensor.
   */
  public function testIncomingMailsSensorPlugin() {

    // Create a user with permissions to create deliverers and run sensors.
    $test_user = $this->drupalCreateUser([
      'monitoring reports',
      'administer monitoring',
      'access administration pages',
      'administer inmail',
    ]);
    $this->drupalLogin($test_user);

    // Add test fetcher.
    $this->drupalGet('admin/config/system/inmail/deliverers/add');

    $page = $this->getSession()->getPage();
    $page->fillField($this->t('Label'), 'Test Test Fetcher');
    $this->assertSession()->waitForText($this->t('Machine name:'));
    $page->pressButton($this->t('Edit'));
    $page->fillField($this->t('Machine-readable name'), 'test_fetcher');
    $page->selectFieldOption($this->t('Plugin'), 'test_fetcher');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $page->pressButton($this->t('Save'));

    $this->drupalGet('admin/config/system/inmail/deliverers');
    $page = $this->getSession()->getPage();
    // Check fetcher status to get the unprocessed mails.
    $page->pressButton($this->t('Check fetcher status'));

    // Run the sensor and test the result. Test fetcher has 100 unprocessed
    // messages by default.
    $this->drupalGet('/admin/reports/monitoring/sensors/inmail_incoming_mails');
    $page = $this->getSession()->getPage();
    $page->pressButton($this->t('Run again'));
    $this->assertSession()->pageTextContains($this->t('@unprocessed unprocessed incoming mails', ['@unprocessed' => 100]));

    // Process fetcher.
    $this->drupalGet('admin/config/system/inmail/deliverers');
    $page = $this->getSession()->getPage();
    $page->pressButton($this->t('Process fetchers'));

    // Run the sensor again and assert result for unprocessed messages.
    $result = monitoring_sensor_run('inmail_incoming_mails', TRUE, TRUE);
    $this->assertEqual($result->getValue(), 99);

    // Set sensor to track processed messages.
    $this->drupalGet('/admin/config/system/monitoring/sensors/inmail_incoming_mails');

    // Select unprocessed mails to track.
    $page = $this->getSession()->getPage();
    // Count type.
    $page->selectFieldOption($this->t('Count'), 'processed');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $page->checkField($this->t('Test Test Fetcher'));
    $page->fillField($this->t('Value Label'), 'processed incoming mails');
    $page->pressButton($this->t('Save'));

    // Run sensor again and assert result for processed messages.
    $result = monitoring_sensor_run('inmail_incoming_mails', TRUE, TRUE);
    $this->assertEqual($result->getValue(), 1);

    // Add a Drush deliverer.
    $this->drupalGet('admin/config/system/inmail/deliverers/add');
    $page = $this->getSession()->getPage();
    $page->fillField($this->t('Label'), 'Test Drush Deliverer');
    $this->assertSession()->waitForText($this->t('Machine name:'));
    $page->pressButton($this->t('Edit'));
    $page->fillField($this->t('Machine-readable name'), 'test_drush');
    $page->selectFieldOption($this->t('Plugin'), 'drush');
    $this->assertSession()->assertWaitOnAjaxRequest();
    $page->pressButton($this->t('Save'));

    // Edit configuration settings and make sure that deliverers are updating.
    $this->drupalGet('/admin/config/system/monitoring/sensors/inmail_incoming_mails');
    $this->assertSession()->pageTextContains($this->t('Test Test Fetcher'));
    $this->assertSession()->pageTextContains($this->t('Test Drush Deliverer'));

    // Select unprocessed mails to track.
    $page = $this->getSession()->getPage();
    // Count type.
    $page->selectFieldOption($this->t('Count'), 'unprocessed');
    $this->assertSession()->assertWaitOnAjaxRequest();
    // Assert that ajax callback has updated deliverers.
    $this->assertSession()->pageTextNotContains($this->t('Test Drush Deliverer'));
    $this->assertSession()->pageTextContains($this->t('Test Test Fetcher'));

  }

}
