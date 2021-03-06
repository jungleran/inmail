<?php

namespace Drupal\Tests\inmail_collect\Functional;

use Drupal\collect\Entity\Container;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\inmail\MIME\MimeHeader;
use Drupal\inmail\MIME\MimeMessage;
use Drupal\inmail\ProcessorResult;
use Drupal\inmail\MIME\MimeHeaderField;
use Drupal\Tests\inmail\Functional\InmailWebTestBase;

/**
 * Tests the presentation of collected messages.
 *
 * @group inmail
 */
class InmailCollectWebTest extends InmailWebTestBase {

  use StringTranslationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['inmail_test', 'inmail_collect', 'block'];

  /**
   * Tests the user interface.
   *
   * @see Drupal\inmail_collect\Plugin\collect\Model\InmailMessage::build()
   */
  public function testUi() {
    $this->drupalPlaceBlock('local_actions_block');
    $this->drupalPlaceBlock('local_tasks_block');
    $this->drupalPlaceBlock('page_title_block');
    // Process and store a message.
    $raw = $this->getMessageFileContents('/bounce/bad-destination-address.eml');
    $deliverer = $this->createTestDeliverer();
    $this->processor->process('unique_key', $raw, $deliverer);
    // Assert success function is called.
    $this->assertSuccess($deliverer, 'unique_key');
    // Log in and view the list.
    $user = $this->drupalCreateUser(['administer collect']);
    $this->drupalLogin($user);
    $this->drupalGet('admin/content/collect');
    $this->assertText('https://www.drupal.org/project/inmail/schema/message');
    $this->assertText(format_date(strtotime('19 Feb 2014 10:05:15 +0100'), 'short'));
    $origin_uri = \Drupal::request()->getSchemeAndHttpHost() . \Drupal::request()->getBasePath() . '/inmail/message/message-id/21386_1392800717_530473CD_21386_78_1_OF72A6C464.8DF6E397-ONC1257C84.0031EBBB-C1257C84.0031=EC2C+@acacia.example.org';
    $this->assertText($origin_uri);
    $this->assertText('application/json');

    // View details as JSON.
    $this->clickLink('View');
    $container_url = $this->getUrl();
    $this->assertText($origin_uri);
    $this->assertText($this->t('There is no plugin configured to display data.'));
    $this->clickLink($this->t('Raw data'));
    $this->assertText('&quot;header-subject&quot;: &quot;DELIVERY FAILURE: User environment (user@example.org) not listed in Domino Directory&quot;');
    $this->assertText('&quot;header-to&quot;: {
        &quot;name&quot;: &quot;&quot;,
        &quot;address&quot;: &quot;bounces+user=example.org@example.com&quot;
    }');
    $this->assertText('&quot;header-from&quot;: [
        {
            &quot;name&quot;: &quot;&quot;,
            &quot;address&quot;: &quot;Postmaster@acacia.example.org&quot;
        }
    ]');
    // '<' and '>' are converted to /u003C and /u003E entities by the formatter.
    $this->assertText('&quot;header-message-id&quot;: &quot;\u003C21386_1392800717_530473CD_21386_78_1_OF72A6C464.8DF6E397-ONC1257C84.0031EBBB-C1257C84.0031=EC2C+@acacia.example.org\u003E&quot;');
    $this->assertText('&quot;deliverer&quot;: &quot;' . $deliverer->id() . '&quot;');
    // Last line of the raw message.
    $this->assertText('--==IFJRGLKFGIR25201654UHRUHIHD--');

    // Create suggested Inmail model and view details as rendered.
    $this->drupalGet($container_url);
    $this->clickLink($this->t('Set up a @label model', ['@label' => 'Email message']));
    $this->drupalPostForm(NULL, ['id' => 'email_message'], $this->t('Save'));
    // Details summaries of each part.
    $details = $this->xpath('//div[@class="field__item"]//details');
    $this->assertEqual((string) $details[0]->summary, 'DELIVERY FAILURE: User environment (user@example.org) not listed in Domino Directory');
    $this->assertEqual((string) $details[0]->div->details[0]->summary, $this->t('Part 1'));
    $this->assertEqual((string) $details[0]->div->details[1]->summary, $this->t('Part 2'));
    $this->assertEqual((string) $details[0]->div->details[2]->summary, $this->t('Part 3'));
    // Eliminate repeated whitespace to simplify matching.
    $this->setRawContent(preg_replace('/\s+/', ' ', $this->getRawContent()));
    // MimeHeader fields.
    $this->assertText($this->t('From :from'), [':from' => 'Postmaster@acacia.example.org']);
    $this->assertText($this->t('To :to'), [':to' => 'bounces+user=example.org@example.com']);
    $this->assertText($this->t('Subject :subject'), [':subject' => ' DELIVERY FAILURE: User environment (user@example.org) not listed in Domino Directory']);
    $this->assertText($this->t('Content-Type :content_type'), [':content_type' => 'multipart/report']);
    $this->assertText($this->t('Content-Type :content_type'), [':content_type' => 'text/plain']);
    $this->assertText($this->t('Content-Type :content_type'), [':content_type' => 'message/delivery-status']);
    $this->assertText($this->t('Content-Type :content_type'), [':content_type' => 'message/rfc822']);
    // Body.
    $this->assertText('Your message Subject: We want a toxic-free future was not delivered to: environment@lvmh.fr');
  }

  /**
   * Tests json encode when given invalid UTF-8/binary data.
   *
   * @see Drupal\inmail_collect\Plugin\inmail\Handler\CollectHandler::invoke()
   */
  public function testInvoke() {
    // Testing json_encode function which should fail for binary data.
    /** @var \Drupal\inmail\Entity\HandlerConfig $handler_config */
    $handler_config = \Drupal::entityTypeManager()->getStorage('inmail_handler')->load('collect');
    /** @var \Drupal\inmail\Plugin\inmail\Handler\HandlerInterface $handler */
    $handler = \Drupal::service('plugin.manager.inmail.handler')->createInstance($handler_config->getPluginId(), $handler_config->getConfiguration());
    $processor_result = new ProcessorResult();
    $deliverer = $this->createTestDeliverer();
    $processor_result->setDeliverer($deliverer);
    // Creating MimeMessage which contains invalid UTF-8 character.
    $message = new MimeMessage(new MimeHeader([
      new MimeHeaderField('Message-ID', "\x80"),
      new MimeHeaderField('Received', 'blah; Thu, 29 Jan 2015 15:43:04 +0100'),
      new MimeHeaderField('Subject', 'Foo'),
      new MimeHeaderField('To', 'bar@example.com'),
      new MimeHeaderField('From', 'foobar@example.com'),
    ]), 'body');
    // Triggering json_encode which should fail.
    $handler->invoke($message, $processor_result);
    $containers = Container::loadMultiple();
    // Load the last data and check that is an empty array.
    $this->assertEqual([], $containers);
  }

}
