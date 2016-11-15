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
   * @return \Drupal\inmail\MIME\Rfc2822Address|null
   *   The 'From' header field address object, or NULL if it does not exist.
   */
  public function getFrom();

  /**
   * Returns the unique message identifier(s).
   *
   * @return \Drupal\inmail\MIME\Rfc2822Address|\Drupal\inmail\MIME\Rfc2822Address[]
   *   The 'References'/'In-Reply-To' header field address object(s), NULL if
   *   it does not exist or it is the same as 'From'.
   */
  public function getReplyTo();

  /**
   * Returns the list of message recipients.
   *
   * @return \Drupal\inmail\MIME\Rfc2822Address[]
   *   List of 'To' recipient address objects.
   */
  public function getTo();

  /**
   * Returns the list of Cc recipients.
   *
   * @return \Drupal\inmail\MIME\Rfc2822Address[]
   *   List of 'Cc' recipient address objects.
   */
  public function getCc();

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
