<?php

namespace Drupal\inmail\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\Tests\inmail\Kernel\InmailTestHelperTrait;

/**
 * Tests the general Inmail mechanism in a typical Drupal email workflow case.
 *
 * @group inmail
 * @requires module past_db
 */
class InmailIntegrationTest extends WebTestBase {

  use DelivererTestTrait, InmailTestHelperTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'inmail_mailmute',
    'field_ui',
    'past_db',
    'past_testhidden',
    'inmail_test',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Set the Inmail processor and parser services.
    $this->processor = \Drupal::service('inmail.processor');
    $this->parser = \Drupal::service('inmail.mime_parser');

    // Make sure new users are blocked until approved by admin.
    $this->config('user.settings')->set('register', USER_REGISTER_VISITORS_ADMINISTRATIVE_APPROVAL)->save();
    // Enable logging of raw mail messages.
    $this->config('inmail.settings')->set('log_raw_emails', TRUE)->save();
  }

  /**
   * Tests that mails are properly displayed using Inmail message element.
   */
  public function testEmailDisplay() {
    $raw_multipart = $this->getMessageFileContents('normal.eml');
    // @todo: Move the XSS part into separate email example.
    $raw_multipart = str_replace('</div>', "<script>alert('xss_attack')</script></div>", $raw_multipart);

    // Create a test user and log in.
    $user = $this->drupalCreateUser([
      'access administration pages',
      'administer inmail',
    ]);
    $this->drupalLogin($user);

    // In reality the message would be passed to the processor through a drush
    // script or a mail deliverer.
    // Process the raw multipart mail message.
    $deliverer = $this->createTestDeliverer();
    $this->processor->process('unique_key', $raw_multipart, $deliverer);

    // Assert the raw message was logged.
    /** @var \Drupal\past\PastEventInterface $event */
    $event = $this->getLastEventByMachinename('process');
    $this->assertEqual($event->getArgument('email')->getData(), $raw_multipart);

    /** @var \Drupal\Inmail\MIME\MimeMessageInterface $message */
    $message = $this->parser->parseMessage($raw_multipart);

    // Test "teaser" view mode of Inmail message element.
    $this->drupalGet('admin/inmail-test/email/' . $event->id() . '/teaser');
    $this->assertText('Email display');
    $this->assertRaw('Arild Matsson');
    $this->assertRaw('Arild Matsson');
    $this->assertRaw('Someone Else');
    $this->assertRaw('BMH testing sample');
    $this->assertText(htmlspecialchars($message->getPlainText(), ENT_QUOTES, 'UTF-8'));

    // Test "full" view mode of Inmail message element.
    $this->drupalGet('admin/inmail-test/email/' . $event->id() . '/full');
    $this->assertText('Email display');
    // @todo Introduce assert helper for fields + body.
    // Parties involved.
    $this->assertText('From');
    $this->assertText('Arild Matsson');
    $this->assertNoText('reply to');
    $this->assertRaw('Arild Matsson');
    $this->assertText('To');
    $this->assertRaw('Arild Matsson');
    $this->assertRaw('Someone Else');
    // Dates
    // Date: Tue, 21 Oct 2014 11:21:01 +0200
    // Received: ...; Tue, 21 Oct 2014 09:21:02 +0000 (UTC)
    // Converted to DST (+1100)
    $this->assertText('Date');
    $this->assertText('2014-10-21 20:21:01');
    $this->assertText('Received');
    $this->assertText('2014-10-21 20:21:02');

    // Assert message parts.
    $this->assertText($message->getPart(0)->getDecodedBody());
    $this->assertText(htmlspecialchars($message->getPlainText()));
    // Script tags are removed for security reasons.
    $this->assertRaw("<div dir=\"ltr\">Hey, it would be really bad for a mail handler to classify this as a bounce just because I have no mailbox outside my house.alert('xss_attack')</div>");

    // By RFC 2822, To header field is not necessary.
    // Load simple malformed message.
    $raw = $this->getMessageFileContents('missing-to-field.eml');
    $deliverer = $this->createTestDeliverer();
    $this->processor->process('unique_key', $raw, $deliverer);
    $event = $this->getLastEventByMachinename('process');
    $this->assertEqual($event->getArgument('email')->getData(), $raw);
    $message = $this->parser->parseMessage($raw);
    $this->drupalGet('admin/inmail-test/email/' . $event->id() . '/full');
    $this->assertText('Email display');
    $this->assertNoText('To');
    // @todo properly assert message fields.
    //$this->assertNoField('To', 'There is no To header field');

    // Test a message with reply to header field.
    $raw = $this->getMessageFileContents('plain-text-reply-to.eml');
    $deliverer = $this->createTestDeliverer();
    $this->processor->process('unique_key', $raw, $deliverer);
    $event = $this->getLastEventByMachinename('process');
    $this->drupalGet('admin/inmail-test/email/' . $event->id() . '/full');
    $this->assertText('Email display');
    // Reply-To participants.
    $this->assertText('reply to');
    $this->assertText('Bobby');
    $this->assertText('Big Brother');
    // Do not display Reply-To in teaser.
    $this->drupalGet('admin/inmail-test/email/' . $event->id() . '/teaser');
    $this->assertNoText('reply to');
    // Do not display Date in teaser.
    $this->assertNoRaw('<label>Date</label>');

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
    $deliverer = $this->createTestDeliverer();
    $processor->process('unique_key', $raw, $deliverer);
    $this->assertSuccess($deliverer, 'unique_key');

    // Check send state. Status code, date and reason are parsed from the
    // generated bounce message.
    $this->drupalGet('user/2');
    $this->assertText('Invalid address');
    $this->assertText('5.1.1');
    $this->assertText('Permanent Failure: Bad destination mailbox address');
    $this->assertText('2015-01-29 15:43:04 +01:00');
    $this->assertText('This didn\'t go too well.');

    $deliverer = $this->createTestDeliverer();
    $processor->process('unique_key', NULL, $deliverer);
    // Success function is never called since we pass NULL, thus state is unchanged.
    $this->assertSuccess($deliverer, '');
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

}