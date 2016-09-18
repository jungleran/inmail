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
    // By RFC 5322 format, Date and From header fields are only required fields.
    $result = TRUE;
    foreach (['Received', 'From'] as $key) {
      $counter = count($this->getHeader()->getFieldBodies($key));
      // If the field is absent, save error message in array.
      if (!$this->getHeader()->hasField($key)) {
        $result = FALSE;
        $this->error_messages[$key] = 'Missing ' . $key . ' Field';
      }
      // Count number of field bodies. According to RFC, there should be only one
      // occurrence of fields Received and From.
      if ($counter > 1) {
        $result = FALSE;
        $this->error_messages[$key] = $counter . ' ' . $key . ' Fields';
      }
      $counter = 0;
    }
    return $result;
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
