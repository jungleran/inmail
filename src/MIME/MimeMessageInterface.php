<?php

namespace Drupal\inmail\MIME;

/**
 * Provides methods for a MIME email message.
 *
 * @ingroup mime
 */
interface MimeMessageInterface extends MimeEntityInterface {

  /**
   * Returns the Message-Id.
   *
   * The RFC declares that the Message-Id field "should" be set, but it is not
   * required. The value has the format "<id-left@id-right>"
   *
   * @see http://tools.ietf.org/html/rfc5322#section-3.6.4
   *
   * @return string|null
   *   The body of the Message-Id field, or NULL if it is not set.
   */
  public function getMessageId();

  /**
   * Returns the message subject.
   *
   * @return string|null
   *   The content of the 'Subject' header field, or null if that field does
   *   not exist.
   */
  public function getSubject();

  /**
   * Returns the message sender.
   *
   * @param bool $decode
   *   Optional value to indicate if header is in punycode form.
   *
   * @return array[]|null
   *   The 'From' header field body, or null if inexisting.
   */
  public function getFrom($decode = FALSE);

  /**
   * Returns the unique message identifier(s).
   *
   * @param bool $decode
   *   (optional) The value to indicate if the header is in punycode form.
   *
   * @return string|null
   *   The 'References'/'In-Reply-To' header field body, NULL if it does not
   *   exist or it is the same as 'From'.
   */
  public function getReplyTo($decode = FALSE);

  /**
   * Returns the list of message recipients.
   *
   * @param bool $decode
   *   Optional value to indicate if header is in punycode form.
   *
   * @return array[]|null
   *   List of 'To' recipient addresses.
   */
  public function getTo($decode = FALSE);

  /**
   * Returns the list of Cc recipients.
   *
   * @param bool $decode
   *   Optional value to indicate if header is in punycode form.
   *
   * @return array[]|null
   *   List of 'Cc' recipient addresses.
   */
  public function getCc($decode = FALSE);

  /**
   * Returns the date when the message was received by the recipient.
   *
   * @return \Drupal\Component\Datetime\DateTimePlus|null
   *   The received date from the header or null if not found.
   */
  public function getReceivedDate();

  /**
   * Extracts plaintext representation of body.
   *
   * This method is no longer used for the mail display.
   * Use \Drupal\inmail\MIME\MimeMessageDecompositionInterface::getBodyPaths()
   * and its "plain" output instead.
   *
   * @return string
   *   Resulting plain texts of body, otherwise empty string.
   */
  public function getPlainText();

  /**
   * Extracts HTML body representation.
   *
   * This method is no longer used for the mail display.
   * Use \Drupal\inmail\MIME\MimeMessageDecompositionInterface::getBodyPaths()
   * and its "html" output instead.
   *
   * @return string
   *   Resulting string contains HTML markup for the message body.
   */
  public function getHtml();

  /**
   * Returns the date when the message was sent.
   *
   * @return string
   *   The content of the 'Date' header field.
   */
  public function getDate();

}
