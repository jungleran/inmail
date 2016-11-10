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
  public static $modules = ['inmail_test', 'past_db'];

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
   * Tests the message parts of the Inmail Message element.
   */
  public function testMessageParts() {
    $this->doTestHtmlBodyPart();
    $this->doTestComplexAttachments();
    $this->doTestUnknownParts();
    $this->doTestMailAttachmentPdf();
  }

  /**
   * Tests the HTML body part of the message.
   */
  public function doTestHtmlBodyPart() {
    // Load and process the HTML-only mail message.
    $raw_html = $this->getMessageFileContents('html-text.eml');
    $this->processor->process('unique_key', $raw_html, $this->createTestDeliverer());
    $event = $this->getLastEventByMachinename('process');

    // @todo Test headers for the HTML-only mail example.
    // Test 'full' view mode of the HTML-only message.
    $this->drupalGet('admin/inmail-test/email/' . $event->id() . '/full');
    // There should be two tabs since the plain text is generated from HTML.
    $this->assertRaw('<a href="#inmail-message__body__html">HTML</a>');
    $this->assertRaw('<a href="#inmail-message__body__content">Plain</a>');
    // Assert new line separators are replaced with "<br>" tags.
    $this->assertRaw('<div class="inmail-message__element inmail-message__body__content" id="inmail-message__body__content">Hey Alice,<br />
  Skype told me its your birthday today? Congratulations!<br />
  Wish I could be there and celebrate with you...<br />
  Sending you virtual cake, french cheese and champagne (without alcohol, just for you!) :P<br />
  Cheerious,<br />
  Bob</div>');
    // Assert the markup inside "HTML" tab.
    $this->assertRaw('<div class="inmail-message__element inmail-message__body__html" id="inmail-message__body__html"><div dir="ltr">
  <p>Hey Alice,</p>
  <p>Skype told me its your birthday today? Congratulations!</p>
  <p>Wish I could be there and celebrate with you...</p>
  <p>Sending you virtual cake, french cheese and champagne (without alcohol, just for you!) :P</p>
  <p>Cheerious,</p>
  <p>Bob</p>
</div></div>');

    // Test 'teaser' view mode of the HTML-only message.
    $this->drupalGet('admin/inmail-test/email/' . $event->id() . '/teaser');
    $this->assertNoRaw('<div class="inmail-message__element inmail-message__body__html" id="inmail-message__body__html">');
    $this->assertRaw('<div class="inmail-message__element inmail-message__body__content" id="inmail-message__body__content">Hey Alice,
  Skype told me its your birthday today? Congratulations!
  Wish I could be there and celebrate with you...
  Sending you virtual cake, french cheese and champagne (without alcohol, just for you!) :P
  Cheerious,
  Bob</div>');
  }

  /**
   * Tests the complex attachment variant.
   */
  public function doTestComplexAttachments() {
    $raw_email_with_attachments = $this->getMessageFileContents('attachments/multiple-attachments.eml');

    // Process the raw multipart mail message.
    $this->processor->process('key', $raw_email_with_attachments, $this->createTestDeliverer());
    $event_id = $this->getLastEventIdByMachinename('process');

    // Go to the "full" view mode page.
    $this->drupalGet('admin/inmail-test/email/' . $event_id . '/full');
    // Assert the default empty subject text is displayed.
    $this->assertText('(no subject)');

    // Assert attachment file names and size.
    $this->assertLink('hello.txt');
    $this->assertText('hello.txt (61 bytes)');
    // @todo: Properly assert special characters in file names
    //    https://www.drupal.org/node/2819645.
    $this->assertText('This is a sample image with');
    $this->assertText('.JPEG.png (94 bytes)');
    $this->assertText('Inline image.png (94 bytes)');
    $this->assertRaw('<img src="' . base_path() . 'inmail-test/email/' . $event_id . '/0_0_1/download" width="9" height="9">');
    $this->clickLink('Inline image.png');
    $this->assertResponse(200);
    $this->assertHeader('Content-Disposition', 'inline; filename="Inline image.png"');
    $this->drupalGet('admin/inmail-test/email/' . $event_id . '/full');

    // Assert the download link response.
    $this->clickLink('hello.txt');
    $this->assertResponse(200);
    $this->assertText('Greetings from Inmail attachment display');
    $this->assertHeader('Content-Type', 'text/plain; charset=UTF-8; name="hello.txt"');
    $this->assertHeader('Content-Disposition', 'attachment; filename="hello.txt"');
    $this->assertHeader('Content-Transfer-Encoding', 'base64');

    $this->drupalGet('admin/inmail-test/email/' . $event_id . '/full');
    $this->clickLink(t('Download raw message'));
    $this->assertResponse(200);
    $this->assertHeader('Content-Type', 'message/rfc822');
    $this->assertHeader('Content-Disposition', 'attachment; filename=original_message.eml');
    $this->assertText('This is an email with attachments.');
    $this->assertText('Content-Type: text/plain');

    // Go to the "teaser" view mode page.
    $this->drupalGet('admin/inmail-test/email/' . $event_id . '/teaser');
    // Assert the default empty subject text is displayed.
    $this->assertText('(no subject)');
  }

  /**
   * Tests unknown parts of an email.
   */
  public function doTestUnknownParts() {
    $raw_email_with_attachments = $this->getMessageFileContents('attachments/multiple-attachments.eml');

    // Process the raw multipart mail message.
    $this->processor->process('key', $raw_email_with_attachments, $this->createTestDeliverer());
    $event_id = $this->getLastEventIdByMachinename('process');

    // Go to the "full" view mode page.
    $this->drupalGet('admin/inmail-test/email/' . $event_id . '/full');

    // Assert unknown parts.
    $this->assertText('Unknown parts');
    $this->assertLink('multipart/mixed');
    $this->assertLink('multipart/related');
    $this->assertLink('multipart/alternative');
    $this->clickLink('application/x-unknown');
    $this->assertResponse(200);
    $this->assertText('Unknown part');
  }

  /**
   * Tests pdf attachment of an email.
   */
  public function doTestMailAttachmentPdf() {
    // Test PDF attachment download link response and assert headers.
    $raw_email_with_attachments = $this->getMessageFileContents('attachments/pdf-attachment.eml');
    $this->processor->process('key', $raw_email_with_attachments, $this->createTestDeliverer());
    $event_id = $this->getLastEventIdByMachinename('process');
    $this->drupalGet('admin/inmail-test/email/' . $event_id . '/full');
    $this->assertText('sample.pdf (1.41 KB)');
    $this->clickLink('sample.pdf');
    $this->assertResponse(200);
    $this->assertHeader('Content-Type', 'application/pdf; name="sample.pdf"');
    $this->assertHeader('Content-Disposition', 'attachment; filename="sample.pdf"');
    $this->assertHeader('Content-Transfer-Encoding', 'base64');
  }

}
