<?php

namespace Drupal\inmail\Tests;

use Drupal\inmail\Entity\DelivererConfig;
use Drupal\simpletest\WebTestBase;

/**
 * Tests the general Inmail mechanism in a typical Drupal email workflow case.
 *
 * @group inmail
 */
class IntegrationTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'inmail_mailmute',
    'field_ui',
    'past',
    'past_db',
    'past_testhidden',
    'inmail_test',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Make sure new users are blocked until approved by admin.
    \Drupal::configFactory()->getEditable('user.settings')
      ->set('register', USER_REGISTER_VISITORS_ADMINISTRATIVE_APPROVAL)
      ->save();
    // Enable logging of raw mail messages.
    \Drupal::configFactory()->getEditable('inmail.settings')
      ->set('log_raw_emails', TRUE)
      ->save();
  }

  /**
   * Tests that mails are properly displayed using Inmail message element.
   */
  public function testEmailDisplay() {
    $regular = drupal_get_path('module', 'inmail_test') . '/eml/normal.eml';
    $raw_multipart = file_get_contents(DRUPAL_ROOT . '/' . $regular);

    // Create a test user and log in.
    $user = $this->drupalCreateUser([
      'access administration pages',
      'administer inmail',
    ]);
    $this->drupalLogin($user);

    // In reality the message would be passed to the processor through a drush
    // script or a mail deliverer.
    /** @var \Drupal\inmail\MessageProcessorInterface $processor */
    $processor = \Drupal::service('inmail.processor');
    \Drupal::state()->set('inmail.test.success', '');
    // Process the raw multipart mail message.
    $processor->process('unique_key', $raw_multipart, DelivererConfig::create(['id' => 'test']));

    // Assert the raw message was logged.
    /** @var \Drupal\past\PastEventInterface $event */
    $event = $this->getLastEventByMachinename('process');
    $this->assertEqual($event->getArgument('email')->getData(), $raw_multipart);

    /** @var \Drupal\inmail\MIME\Parser $parser */
    $parser = \Drupal::service('inmail.mime_parser');
    $message = $parser->parseMessage($raw_multipart);

    // Test "teaser" view mode of Inmail message element.
    $this->drupalGet('admin/inmail-test/email/' . $event->id() . '/teaser');
    $this->assertText('Email display');
    $this->assertRaw(htmlspecialchars($message->getFrom()));
    $this->assertRaw(htmlspecialchars($message->getTo()[0]));
    $this->assertRaw(htmlspecialchars($message->getCc()[0]));
    $this->assertRaw(htmlspecialchars($message->getSubject()));
    $this->assertRaw(htmlspecialchars($message->getReceivedDate()));
    $this->assertText(htmlspecialchars($message->getPlainText(), ENT_QUOTES, 'UTF-8'));

    // Test "full" view mode of Inmail message element.
    $this->drupalGet('admin/inmail-test/email/' . $event->id() . '/full');
    $this->assertText('Email display');
    // @todo Introduce assert helper for fields + body.
    $this->assertText('Received');
    $this->assertText($message->getReceivedDate());
    $this->assertText('From');
    $this->assertText(htmlspecialchars($message->getFrom()));
    $this->assertText('To');
    $this->assertText(htmlspecialchars(implode(', ', $message->getTo())));
    $this->assertText('Cc');
    $this->assertText(htmlspecialchars(implode(', ', $message->getCc())));
    // Assert message parts.
    $this->assertText('plain');
    $this->assertText('html');
    $this->assertText('Header');
    $this->assertText($message->getPart(0)->getHeader()->toString());
    $this->assertText($message->getPart(0)->getDecodedBody());
    $this->assertText('Header');
    $this->assertText($message->getPart(1)->getHeader()->toString());
    $this->assertText(htmlspecialchars($message->getPart(1)->getDecodedBody()));

    // By RFC 2822, To header field is not necessary.
    // Load simple malformed message.
    $regular = drupal_get_path('module', 'inmail_test') . '/eml/missing-to-field.eml';
    $raw = file_get_contents(DRUPAL_ROOT . '/' . $regular);
    $processor->process('unique_key', $raw, DelivererConfig::create(['id' => 'test']));
    $event = $this->getLastEventByMachinename('process');
    $this->assertEqual($event->getArgument('email')->getData(), $raw);
    $message = $parser->parseMessage($raw);
    $this->drupalGet('admin/inmail-test/email/' . $event->id() . '/full');
    $this->assertText('Email display');
    $this->assertNoText('To');
    // @todo properly assert message fields.
    //$this->assertNoField('To', 'There is no To header field');

    // Testing the access to past event created by non-inmail module.
    // @see \Drupal\inmail_test\Controller\EmailDisplayController.
    $event = past_event_create('past', 'test1', 'Test log entry');
    $event->save();
    $this->drupalGet('admin/inmail-test/email/' . $event->id());
    // Should be thrown NotFoundHttpException.
    $this->assertResponse(404);
    $this->assertText('Page not found');
  }

  /**
   * Tests the Inmail + Mailmute mechanism with a hard bounce for a user.
   */
  public function testBounceFlow() {
    // A new user registers.
    $register_edit = array(
      // Oh no, the email address was misspelled!
      'mail' => 'usre@example.org',
      'name' => 'user',
    );
    $this->drupalPostForm('user/register', $register_edit, 'Create new account');
    $this->assertText('Your account is currently pending approval by the site administrator.');

    // Admin activates the user, thereby sending an approval email.
    $admin = $this->drupalCreateUser(array(
      'administer users',
      'administer user display',
      'administer mailmute',
    ));
    $this->drupalLogin($admin);
    $approve_edit = array(
      'status' => '1',
    );
    $this->drupalPostForm('user/2/edit', $approve_edit, 'Save');
    $this->assertMail('subject', 'Account details for user at Drupal (approved)');

    // Fake a bounce.
    $sent_mails = $this->drupalGetMails();
    $raw = static::generateBounceMessage(array_pop($sent_mails));
    // In reality the message would be passed to the processor through a drush
    // script or a mail deliverer.
    /** @var \Drupal\inmail\MessageProcessorInterface $processor */
    $processor = \Drupal::service('inmail.processor');
    \Drupal::state()->set('inmail.test.success', '');
    $processor->process('unique_key', $raw, DelivererConfig::create(array('id' => 'test')));
    $this->assertEqual(\Drupal::state()->get('inmail.test.success'), 'unique_key');

    // Check send state. Status code, date and reason are parsed from the
    // generated bounce message.
    $this->drupalGet('user/2');
    $this->assertText('Invalid address');
    $this->assertText('5.1.1');
    $this->assertText('Permanent Failure: Bad destination mailbox address');
    $this->assertText('2015-01-29 15:43:04 +01:00');
    $this->assertText('This didn\'t go too well.');

    \Drupal::state()->set('inmail.test.success', '');
    $processor->process('unique_key', NULL, DelivererConfig::create(['id' => 'test']));
    // Success function is never called since we pass NULL, thus state is unchanged.
    $this->assertEqual(\Drupal::state()->get('inmail.test.success'), '');
    $event = $this->getLastEventByMachinename('process');
    $this->assertNotNull($event);
    $this->assertEqual($event->getMessage(), 'Incoming mail, parsing failed with error: Failed to split header from body');
  }

  /**
   * Returns a sample bounce message with values from a message.
   *
   * The returned string will look like a typical hard bounce, as if the
   * original message was sent to an email server that failed to forward it to
   * its destination.
   *
   * @param array $original_message
   *   The original non-bounce message in the form used by MailManager::mail().
   *
   * @return string
   *   The generated bounce message.
   */
  protected static function generateBounceMessage(array $original_message) {
    // Set replacement variables.
    $from = $original_message['from'];
    $subject = $original_message['subject'];
    $body = $original_message['body'];
    $return_path = $original_message['headers']['Return-Path'];
    $to = preg_replace('/<(.*)>/', '$1', $original_message['to']);
    $to_domain = explode('@', $to)[1];

    // Put together the headers.
    $headers = $original_message['headers'] + array(
      'To' => $to,
      'Subject' => $subject,
    );
    foreach ($headers as $name => $body) {
      $headers[$name] = "$name: $body";
    }
    $headers = implode("\n", $headers);

    // Return a fake bounce with values inserted.
    return <<<EOF
Return-Path: <>
Delivered-To: $return_path
Received: some info;
  Thu, 29 Jan 2015 15:43:04 +0100
From: mta@$to_domain
To: $return_path
Subject: Mailbox $to does not exist
Content-Type: multipart/report; report-type=delivery-status; boundary="BOUNDARY"


--BOUNDARY
Content-Description: Notification
Content-Type: text/plain

This didn't go too well.

--BOUNDARY
Content-Type: message/delivery-status

Reporting-MTA: dns;$to_domain

Final-Recipient: rfc822;$to
Action: failed
Status: 5.1.1

--BOUNDARY
Content-Type: message/rfc822

$headers

$body

--BOUNDARY--

EOF;

  }

  /**
   * Returns the last event with a given machine name.
   *
   * @param string $machine_name
   *
   * @return PastEventInterface
   */
  public function getLastEventByMachinename($machine_name) {
    $event_id = db_query_range('SELECT event_id FROM {past_event} WHERE machine_name = :machine_name ORDER BY event_id DESC', 0, 1, array(':machine_name' => $machine_name))->fetchField();
    if ($event_id) {
      return \Drupal::entityManager()
        ->getStorage('past_event')
        ->load($event_id);
    }
  }

}
