<?php

namespace Drupal\inmail\MIME;

use Drupal\Component\Datetime\DateTimePlus;

/**
 * Models an email message.
 *
 * @ingroup mime
 */
class Message extends Entity implements MessageInterface {

  use MessageTrait;

  /**
   * {@inheritdoc}
   */
  public function getMessageId() {
    return $this->getHeader()->getFieldBody('Message-Id');
  }

  /**
   * {@inheritdoc}
   */
  public function getSubject() {
    return $this->getHeader()->getFieldBody('Subject');
  }

  /**
   * {@inheritdoc}
   */
  public function getReceivedDate() {
    // A message has one or more Received header fields. The first occurring is
    // the latest added. Its body has two parts separated by ';', the second
    // part being a date.
    $received_body = $this->getHeader()->getFieldBody('Received');
    list($info, $date_string) = explode(';', $received_body, 2);
    return new DateTimePlus($date_string);
  }

  /**
   * Check that the message complies to the RFC standard.
   *
   * @return bool
   *   TRUE if message is valid, otherwise FALSE.
   */
  public function validate() {
    $valid = TRUE;
    // RFC 5322 specifies Date and From header fields as only required fields.
    // @See https://tools.ietf.org/html/rfc5322#section-3.6
    foreach (['Date', 'From'] as $field_name) {
      // If the field is absent, set the validation error.
      if (!$this->getHeader()->hasField($field_name)) {
        $this->setValidationError($field_name, "Missing $field_name field.");
        $valid = FALSE;
      }
      // There should be only one occurrence of Date and From fields.
      elseif (($count = count($this->getHeader()->getFieldBodies($field_name))) > 1) {
        $this->setValidationError($field_name, "Only one occurrence of $field_name field is allowed. Found $count.");
        $valid = FALSE;
      }
    }
    return $valid;
  }

  /**
   * {@inheritdoc}
   */
  public function getPlainText() {
    $content_fields = $this->getContentType();
    $content_type = $content_fields['type'] . '/' . $content_fields['subtype'] ;
    if ($content_type == 'text/plain') {
      return $this->getDecodedBody();
    }
    else if ($content_type == 'text/html') {
      return strip_tags($this->getDecodedBody());
    }
    return '';
  }

}
