<?php

namespace Drupal\inmail\MIME;

/**
 * Models an email message.
 *
 * @ingroup mime
 */
class MimeMessage extends MimeEntity implements MimeMessageInterface {

  use MimeMessageTrait;

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

  /**
   * {@inheritdoc}
   */
  public function getHtml() {
    $content_type = $this->getContentType()['type'] . '/' . $this->getContentType()['subtype'];
    return $content_type == 'text/html' ? $this->getDecodedBody() : '';
  }

}
