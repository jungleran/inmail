<?php

namespace Drupal\Tests\inmail\Kernel;

use Drupal\Core\Test\AssertMailTrait;
use Drupal\inmail\Entity\DelivererConfig;
use Drupal\inmail\Entity\HandlerConfig;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests the Moderator Forward handler plugin.
 *
 * @group inmail
 */
class ModeratorForwardTest extends KernelTestBase {
  use AssertMailTrait;

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
    $processor->process($bounce, DelivererConfig::create(array('id' => 'test')));
    $this->assertMailCount(0);

    // Do not handle if moderator address is unset.
    /** @var \Drupal\inmail\Entity\HandlerConfig $handler_config */
    $handler_config = HandlerConfig::load('moderator_forward');
    $this->assertEqual($handler_config->getConfiguration()['moderator'], '');
    $processor->process($regular, DelivererConfig::create(array('id' => 'test')));
    $this->assertMailCount(0);

    // Do not handle, and log an error, if moderator address is same as intended
    // recipient.
    $handler_config->setConfiguration(array('moderator' => 'user@example.org'))->save();
    // Forge a mail where we recognize recipient but not status.
    $bounce_no_status = str_replace('Status:', 'Foo:', $bounce);
    $processor->process($bounce_no_status, DelivererConfig::create(array('id' => 'test')));
    $this->assertMailCount(0);

    // Check the Past event created by the processor.
    $events = \Drupal::entityTypeManager()->getStorage('past_event')->loadMultiple();
    // Reading last event.
    $last_event = end($events);
    $event_message = $last_event->getMessage();
    $moderator_message = (string) $last_event->getArgument('ModeratorForwardHandler')->getData()[0];
    $this->assertEqual($event_message, '<21386_1392800717_530473CD_21386_78_1_OF72A6C464.8DF6E397-ONC1257C84.0031EBBB-C1257C84.0031=EC2C+@acacia.example.org>');
    $this->assertEqual($moderator_message, 'Moderator <em class="placeholder">user@example.org</em> is bouncing.');

    // Do not handle, and log an error, if the custom X header is set.
    $handler_config->setConfiguration(array('moderator' => 'moderator@example.com'))->save();
    $regular_x = "X-Inmail-Forwarded: ModeratorForwardTest\n" . $regular;
    $processor->process($regular_x, DelivererConfig::create(array('id' => 'test')));
    $this->assertMailCount(0);

    // Again check past event log.
    $events = \Drupal::entityTypeManager()->getStorage('past_event')->loadMultiple();
    $last_event = end($events);
    $event_message = $last_event->getMessage();
    $moderator_message = (string) $last_event->getArgument('ModeratorForwardHandler')->getData()[0];
    $this->assertEqual($event_message, '<CAFZOsfMjtXehXPGxbiLjydzCY0gCkdngokeQACWQOw+9W5drqQ@mail.gmail.com>');
    $this->assertEqual($moderator_message, 'Refused to forward the same email twice (<em class="placeholder">BMH testing sample</em>).');

    // Forward non-bounces if conditions are right.
    $processor->process($regular, DelivererConfig::create(array('id' => 'test')));
    $this->assertMailCount(1);
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
    $processor->process($original, DelivererConfig::create(array('id' => 'test')));
    $messages = $this->getMails(['id' => 'inmail_handler_moderator_forward']);
    $forward = array_pop($messages);

    // Body should be unchanged.
    $this->assertEqual($forward['body'], $original_parsed->getBody(), 'Forwarded message body is unchanged.');

    // Headers should have the correct changes.
    $headers_prefix = "X-Inmail-Forwarded: handler_moderator_forward\n";
    $expected_headers = $original_parsed->getHeader()->toString();
    $expected_headers = str_replace("To: Arild Matsson <inmail_test@example.com>\n", '', $expected_headers);
    $expected_headers = $headers_prefix . $expected_headers;
    $this->assertEqual($forward['raw_headers'], $expected_headers, 'Forwarded message headers have the correct changes.');
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
