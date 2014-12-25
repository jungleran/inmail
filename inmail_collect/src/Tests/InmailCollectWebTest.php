<?php

/**
 * @file
 * Contains \Drupal\inmail_collect\Tests\InmailCollectWebTest.
 */

namespace Drupal\inmail_collect\Tests;

use Drupal\Core\Url;
use Drupal\simpletest\WebTestBase;

/**
 * Tests the presentation of collected messages.
 *
 * @group inmail
 */
class InmailCollectWebTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('inmail_collect');

  /**
   * Disable strict schema checking until schema is updated, https://www.drupal.org/node/2392365
   */
  protected $strictConfigSchema = FALSE;

  /**
   * Tests the user interface.
   */
  public function testUi() {
    // Process and store a message.
    /** @var \Drupal\inmail\MessageProcessor $processor */
    $processor = \Drupal::service('inmail.processor');
    $raw = file_get_contents(\Drupal::root() . '/' . drupal_get_path('module', 'inmail_test') . '/eml/nouser.eml');
    $processor->process($raw);

    // Log in and view the list.
    $user = $this->drupalCreateUser(array('administer collect'));
    $this->drupalLogin($user);
    $this->drupalGet('admin/content/collect');
    $this->assertText('https://www.drupal.org/project/inmail/schema/message');
    $this->assertText(format_date(strtotime('19 Feb 2014 10:05:15 +0100'), 'short'));
    $this->assertText(Url::fromUri('base://inmail/message/message-id/21386_1392800717_530473CD_21386_78_1_OF72A6C464.8DF6E397-ONC1257C84.0031EBBB-C1257C84.0031EC2C@acacia.example.org', ['absolute' => TRUE]));
    $this->assertText('application/json');

    // View details.
    $this->clickLink('View');
    $this->assertText('"header-subject": "DELIVERY FAILURE: User environment (user@example.org) not listed in\n Domino Directory"');
    $this->assertText('"header-to": "bounces+user=example.org@example.com"');
    $this->assertText('"header-from": "Postmaster@acacia.example.org"');
    // '<' and '>' are converted to &lt; and &gt; entities by the formatter.
    $this->assertText('"header-message-id": "&lt;21386_1392800717_530473CD_21386_78_1_OF72A6C464.8DF6E397-ONC1257C84.0031EBBB-C1257C84.0031EC2C@acacia.example.org&gt;"');
    // Last line of the raw message.
    $this->assertText('--==IFJRGLKFGIR25201654UHRUHIHD--');
  }

}
