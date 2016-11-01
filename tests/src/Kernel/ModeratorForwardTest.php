<?php

namespace Drupal\Tests\inmail\Kernel;

use Drupal\Core\Test\AssertMailTrait;
use Drupal\inmail\Entity\DelivererConfig;
use Drupal\inmail\Entity\HandlerConfig;
use Drupal\inmail\Tests\DelivererTestTrait;
use Drupal\inmail_test\Plugin\inmail\Deliverer\TestDeliverer;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the Moderator Forward handler plugin.
 *
 * @group inmail
 */
class ModeratorForwardTest extends KernelTestBase {
  use AssertMailTrait, DelivererTestTrait;

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = array('inmail', 'inmail_test', 'system', 'user', 'past', 'past_db', 'options');

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installConfig(array('inmail'));
    $this->installEntitySchema('inmail_handler');
    $GLOBALS['config']['system.mail']['interface']['default'] = 'inmail_test_mail_collector';
    \Drupal::configFactory()->getEditable('system.site')
      ->set('mail', 'bounces@example.com')
      ->save();
    $this->installEntitySchema('past_event');
    $this->installSchema('past_db', array('past_event_argument', 'past_event_data'));
  }

  /**
   * Tests the rules for when forwarding should be done.
   */
  public function testModeratorForwardRules() {
    /** @var \Drupal\inmail\MessageProcessor $processor */
    $processor = \Drupal::service('inmail.processor');
    $bounce = $this->getMessageFileContents('nouser.eml');
    $regular = $this->getMessageFileContents('normal.eml');

    // Do not handle if message is bounce.
    // Reset the state to be sure that function is called in the test.
    $deliverer = $this->createTestDeliverer();
    $processor->process('unique_key', $bounce, $deliverer);
    // Message passed all tests, parsings and thus success is called.
    $this->assertSuccess($deliverer, 'unique_key');
    $this->assertMailCount(0);

    // Do not handle if moderator address is unset.
    /** @var \Drupal\inmail\Entity\HandlerConfig $handler_config */
    $handler_config = HandlerConfig::load('moderator_forward');
    $this->assertEqual($handler_config->getConfiguration()['moderator'], '');
    // Reset the state since in previous call it is set.
    $deliverer = $this->createTestDeliverer();
    $processor->process('unique_key', $regular, $deliverer);
    $this->assertSuccess($deliverer, 'unique_key');
    $this->assertMailCount(0);

    // Do not handle, and log an error, if moderator address is same as intended
    // recipient.
    $handler_config->setConfiguration(array('moderator' => 'user@example.org'))->save();
    // Forge a mail where we recognize recipient but not status.
    $bounce_no_status = str_replace('Status:', 'Foo:', $bounce);
    $deliverer = $this->createTestDeliverer();
    $processor->process('unique_key', $bounce_no_status, $deliverer);
    $this->assertSuccess($deliverer, 'unique_key');
    $this->assertMailCount(0);

    // Check the Past event created by the processor.
    $events = \Drupal::entityTypeManager()->getStorage('past_event')->loadMultiple();
    // Reading last event.
    $last_event = end($events);
    $event_message = $last_event->getMessage();
    $moderator_message = (string) $last_event->getArgument('ModeratorForwardHandler')->getData()[0];
    $this->assertEqual($event_message, 'Incoming mail: <21386_1392800717_530473CD_21386_78_1_OF72A6C464.8DF6E397-ONC1257C84.0031EBBB-C1257C84.0031=EC2C+@acacia.example.org>');
    $this->assertEqual($moderator_message, 'Moderator <em class="placeholder">user@example.org</em> is bouncing.');

    // Do not handle, and log an error, if the custom X header is set.
    // Furthermore, if the Received Header states that message is forwarded,
    // do not forward it again. It triggers function invoke().
    $handler_config->setConfiguration(array('moderator' => 'moderator@example.com'))->save();
    $regular_x = "X-Inmail-Forwarded: ModeratorForwardTest\n" . $regular;
    $deliverer = $this->createTestDeliverer();
    $processor->process('unique_key', $regular_x, $deliverer);
    $this->assertSuccess($deliverer, 'unique_key');
    $this->assertMailCount(0);
    $processor->process('unique_key', $regular_x, $deliverer);

    // Again check past event log.
    $events = \Drupal::entityTypeManager()->getStorage('past_event')->loadMultiple();
    $last_event = end($events);
    $event_message = $last_event->getMessage();
    $moderator_message = (string) $last_event->getArgument('ModeratorForwardHandler')->getData()[0];
    $this->assertEqual($event_message, 'Incoming mail: <CAFZOsfMjtXehXPGxbiLjydzCY0gCkdngokeQACWQOw+9W5drqQ@mail.gmail.com>');
    $this->assertEqual($moderator_message, 'Refused to forward the same email twice (<em class="placeholder">BMH testing sample</em>).');

    // Forward non-bounces if conditions are right.
    $deliverer = $this->createTestDeliverer();
    $processor->process('unique_key', $regular, $deliverer);
    $this->assertSuccess($deliverer, 'unique_key');
    $this->assertMailCount(1);

  }

  /**
   * Tests sending message report after processing message.
   */
  public function testSendingReportMessage() {
    /** @var \Drupal\inmail\MessageProcessor $processor */
    $processor = \Drupal::service('inmail.processor');
    $bounce = $this->getMessageFileContents('nouser.eml');
    $regular = $this->getMessageFileContents('normal.eml');
    $deliverer = $this->createTestDeliverer();
    // Testing with bounce.
    $this->assertMailCount(0);
    $deliverer->setMessageReport(1);
    $processor->process('unique_key', $bounce, $deliverer);
    $this->assertMailCount(0);
    // Testing with message witch is not bounce.
    $processor->process('unique_key', $regular, $deliverer);
    $this->assertMailCount(1);
    $mails = $this->getMails();
    $last_mail = $mails[0];
    $this->assertEquals('bounces@example.com', $last_mail['from']);
    $this->assertEquals('Re: BMH testing sample', $last_mail['subject']);
    $this->assertEquals('The message has been processed successfully.', $last_mail['body']);
  }

  /**
   * Tests the forwarded message.
   */
  public function testModeratorForwardMessage() {
    // Get an original.
    $original = $this->getMessageFileContents('normal.eml');
    /** @var \Drupal\inmail\MIME\ParserInterface $parser */
    $parser = \Drupal::service('inmail.mime_parser');
    $original_parsed = $parser->parseMessage($original);

    // Conceive a forward.
    HandlerConfig::load('moderator_forward')
      ->set('configuration', array('moderator' => 'moderator@example.com'))
      ->save();
    /** @var \Drupal\inmail\MessageProcessorInterface $processor */
    $processor = \Drupal::service('inmail.processor');
    $deliverer = $this->createTestDeliverer('test_fetcher');
    $processor->process('unique_key', $original, $deliverer);
    // Assert that message is successfully processed.
    $this->assertSuccess($deliverer, 'unique_key');
    $messages = $this->getMails(['id' => 'inmail_handler_moderator_forward']);
    $forward = array_pop($messages);

    // Body should be unchanged.
    $this->assertEqual($forward['body'], $original_parsed->getBody(), 'Forwarded message body is unchanged.');

    // Headers should have the correct changes.
    $forward_header = "X-Inmail-Forwarded: handler_moderator_forward\n";
    $expected_headers = $original_parsed->getHeader()->toString();
    $expected_headers = str_replace("To: Arild Matsson <inmail_test@example.com>\n", '', $expected_headers);
    // Extract the time from original message and append it.
    $received_header = "Received: by localhost via inmail with test_fetcher " . $deliverer->id() . " id\n <CAFZOsfMjtXehXPGxbiLjydzCY0gCkdngokeQACWQOw+9W5drqQ@mail.gmail.com>;" . substr($forward['received'], strpos($forward['received'], ';')+1) . "\n";
    // Wrap the received header to 78 characters.
    $expected_headers = $forward_header . wordwrap($received_header, 78, "\n ") . $expected_headers;
    // Wrap to 78 characters to match original message.
    $this->assertEqual($forward['raw_headers']->toString(), $expected_headers, 'Forwarded message headers have the correct changes.');
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
    $path = drupal_get_path('module', 'inmail_test') . '/eml/' . $filename;
    return file_get_contents(DRUPAL_ROOT . '/' . $path);
  }

  /**
   * Counts the number of sent mail and compares to an expected value.
   */
  protected function assertMailCount($expected, $message = '', $group = 'Other') {
    $this->assertEqual(count($this->getMails()), $expected, $message, $group);
  }

}
