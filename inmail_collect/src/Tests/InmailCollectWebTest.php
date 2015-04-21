<?php

/**
 * @file
 * Contains \Drupal\inmail_collect\Tests\InmailCollectWebTest.
 */

namespace Drupal\inmail_collect\Tests;

use Drupal\collect\Entity\SchemaConfig;
use Drupal\Core\Url;
use Drupal\inmail\Entity\DelivererConfig;
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
   * Tests the user interface.
   *
   * @see Drupal\inmail_collect\Plugin\collect\Schema\InmailMessageSchema::build()
   */
  public function testUi() {
    // Process and store a message.
    /** @var \Drupal\inmail\MessageProcessor $processor */
    $processor = \Drupal::service('inmail.processor');
    $raw = file_get_contents(\Drupal::root() . '/' . drupal_get_path('module', 'inmail_test') . '/eml/nouser.eml');
    $processor->process($raw, DelivererConfig::create(array('id' => 'test')));

    // Log in and view the list.
    $user = $this->drupalCreateUser(array('administer collect'));
    $this->drupalLogin($user);
    $this->drupalGet('admin/content/collect');
    $this->assertText('https://www.drupal.org/project/inmail/schema/message');
    $this->assertText(format_date(strtotime('19 Feb 2014 10:05:15 +0100'), 'short'));
    $this->assertText(Url::fromUri('base:inmail/message/message-id/21386_1392800717_530473CD_21386_78_1_OF72A6C464.8DF6E397-ONC1257C84.0031EBBB-C1257C84.0031EC2C@acacia.example.org', ['absolute' => TRUE])->toString());
    $this->assertText('application/json');

    // View details as JSON.
    SchemaConfig::load('inmail_message')->disable()->save();
    $this->clickLink('View');
    $this->assertText('&quot;header-subject&quot;: &quot;DELIVERY FAILURE: User environment (user@example.org) not listed in Domino Directory&quot;');
    $this->assertText('&quot;header-to&quot;: &quot;bounces+user=example.org@example.com&quot;');
    $this->assertText('&quot;header-from&quot;: &quot;Postmaster@acacia.example.org&quot;');
    // '<' and '>' are converted to &lt; and &gt; entities by the formatter.
    $this->assertText('&quot;header-message-id&quot;: &quot;&lt;21386_1392800717_530473CD_21386_78_1_OF72A6C464.8DF6E397-ONC1257C84.0031EBBB-C1257C84.0031EC2C@acacia.example.org&gt;&quot;');
    $this->assertText('&quot;deliverer&quot;: &quot;test&quot;');
    // Last line of the raw message.
    $this->assertText('--==IFJRGLKFGIR25201654UHRUHIHD--');

    // View details as rendered.
    SchemaConfig::load('inmail_message')->enable()->save();
    $this->drupalGet($this->getUrl());
    // Details summaries of each part.
    $this->assertFieldByXPath('//div[@class="field-item"]/details/div/details/summary', 'DELIVERY FAILURE: User environment (user@example.org) not listed in Domino Directory');
    $this->assertFieldByXPath('//div[@class="field-item"]/details/div/details/div/details[1]/summary', t('Part 1'));
    $this->assertFieldByXPath('//div[@class="field-item"]/details/div/details/div/details[2]/summary', t('Part 2'));
    $this->assertFieldByXPath('//div[@class="field-item"]/details/div/details/div/details[3]/summary', t('Part 3'));
    // Eliminate repeated whitespace to simplify matching.
    $this->setRawContent(preg_replace('/\s+/', ' ', $this->getRawContent()));
    // Header fields.
    $this->assertText(t('From') . ' Postmaster@acacia.example.org');
    $this->assertText(t('To') . ' bounces+user=example.org@example.com');
    $this->assertText(t('Subject') . ' DELIVERY FAILURE: User environment (user@example.org) not listed in Domino Directory');
    $this->assertText(t('Content-Type') . ' multipart/report');
    $this->assertText(t('Content-Type') . ' text/plain');
    $this->assertText(t('Content-Type') . ' message/delivery-status');
    $this->assertText(t('Content-Type') . ' message/rfc822');
    // Body.
    $this->assertText('Your message Subject: We want a toxic-free future was not delivered to: environment@lvmh.fr');
  }

}
