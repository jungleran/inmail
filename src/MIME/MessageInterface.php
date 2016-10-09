<?php

namespace Drupal\inmail\MIME;

/**
 * Provides methods for a MIME email message.
 *
 * @ingroup mime
 */
interface MessageInterface extends EntityInterface {

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
   *   The content of the 'Subject' header field, or null if that field does not
   *   exist.
   */
  public function getSubject();

  /**
   * Returns the message sender.
   *
   * @param bool $decode
   *   Optional value to indicate if header is in punycode form.
   *
   * @return string|null
   *   The content of the 'From' header field, or null if that field does not
   *   exist.
   */
  public function getFrom($decode = FALSE);

  /**
   * Returns the message recipient.
   *
   * @param bool $decode
   *   Optional value to indicate if header is in punycode form.
   *
   * @return string|null
   *   The content of the 'To' header field, or null if that field does not
   *   exist.
   */
  public function getTo($decode = FALSE);

  /**
   * Returns the date when the message was received by the recipient.
   *
   * @return \Drupal\Component\DateTime\DateTimePlus
   *   The date from the header.
   */
  public function getReceivedDate();

  /**
   * Extracts plaintext representation of body.
   *
   * @return string
   *   Resulting plain texts of body, otherwise empty string.
   */
  public function getPlainText();

}
