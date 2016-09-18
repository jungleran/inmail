<?php

namespace Drupal\inmail\MIME;

use Drupal\Component\Datetime\DateTimePlus;

/**
 * A multipart message.
 *
 * This is the combination of \Drupal\collect\MIME\MultipartEntity and
 * \Drupal\collect\MIME\Message.
 */
class MultipartMessage extends MultipartEntity implements MessageInterface {

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
   * {@inheritdoc}
   */
  public function getPlainText() {
    $message_parts = $this->getParts();
    foreach ($message_parts as $key => $part) {
      $content_fields = $message_parts[$key]->getContentType();
      $content_type = $content_fields['type'] . '/' . $content_fields['subtype'] ;
      $body = $message_parts[$key]->getDecodedBody();
      if ($content_type == 'text/plain') {
        return $body;
      }
      else if ($content_type == 'text/html') {
        return strip_tags($body);
      }
    }
    return '';
  }

}
