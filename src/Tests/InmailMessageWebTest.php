<?php

namespace Drupal\inmail\Tests;

/**
 * Provides common helper methods for Inmail web tests.
 *
 * @group inmail
 */
class InmailMessageWebTest extends InmailWebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['inmail_test', 'block', 'past_db'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Create a test user and log in.
    $user = $this->drupalCreateUser([
      'access administration pages',
      'administer inmail',
    ]);
    $this->drupalLogin($user);

    // Enable message logging in order to test email display.
    $this->config('inmail.settings')->set('log_raw_emails', TRUE)->save();
  }

  /**
   * Tests the attachments part of the Inmail Message element.
   */
  public function testAttachments() {
    $this->doTestComplexAttachments();
  }

  /**
   * Tests the complex attachment variant.
   */
  public function doTestComplexAttachments() {
    $raw_email_with_attachments = $this->getMessageFileContents('attachments/complex.eml');

    // Process the raw multipart mail message.
    $this->processor->process('key', $raw_email_with_attachments, $this->createTestDeliverer());
    $event_id = $this->getLastEventByMachinename('process', TRUE);

    // Go to the "full" view mode page.
    $this->drupalGet('admin/inmail-test/email/' . $event_id . '/full');

    // Assert attachment file names and size.
    $this->assertText('hello.txt (61 B)');
    // @todo: Properly assert special characters in file names
    //    https://www.drupal.org/node/2819645.
    $this->assertText('This is a sample image with');
    $this->assertText('.JPEG.png (94 B)');
    $this->assertText('Inline image.png (94 B)');
  }

}
