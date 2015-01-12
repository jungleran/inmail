<?php
/**
 * @file
 * Contains \Drupal\inmail_collect\Plugin\inmail\Handler\CollectHandler.
 */

namespace Drupal\inmail_collect\Plugin\inmail\Handler;

use Drupal\collect\Entity\Container;
use Drupal\Core\Url;
use Drupal\inmail\MIME\EntityInterface;
use Drupal\inmail\Plugin\inmail\Handler\HandlerBase;
use Drupal\inmail\ProcessorResultInterface;

/**
 * Stores messages in Collect containers.
 *
 * The inmail collect schema is considered unstable.
 *
 * @ingroup handler
 *
 * @Handler(
 *   id = "collect",
 *   label = @Translation("Collect messages")
 * )
 */
class CollectHandler extends HandlerBase {

  /**
   * {@inheritdoc}
   */
  public function help() {
    return array(
      '#markup' => $this->t('The Collect handler stores all messages.'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function invoke(EntityInterface $message, ProcessorResultInterface $processor_result) {
    // For successful processing, a message needs to follow the standards.
    // Some aspects are critical. Check them and cancel otherwise and log.
    // @todo Check for present fields Date, From, To

    // By RFC 5322 (and its predecessors), the uniqueness of the Message-Id
    // field is guaranteed by the host that generates it. While uuid offers
    // more robust uniqueness, Message-Id is preferred because it is defined
    // also outside the domains of Inmail and Collect.

    // Remove brackets from RFC822 message-id format "<" addr-spec ">"
    $message_id = trim($message->getHeader()->getFieldBody('Message-Id'), '<>');

    if (!empty($message_id)) {
      // @todo Formally document this uri pattern.
      $origin_uri = Url::fromUri('base://inmail/message/message-id/'
        . $message_id, ['absolute' => TRUE]);
    }
    else {
      // @todo Formally document this uri pattern.
      $origin_uri = Url::fromUri('base://inmail/message/uuid/'
        . \Drupal::service('uuid')->generate(), ['absolute' => TRUE]);
    }

    // The data to store. Includes the whole message string for completeness,
    // and a few regular and useful header fields.
    $data = array(
      // Note the Subject field is optional by RFC882.
      'header-subject' => $message->getHeader()->getFieldBodyUnfiltered('Subject'),
      'header-to' => $message->getHeader()->getFieldBody('To'),
      'header-from' => $message->getHeader()->getFieldBody('From'),
      'header-message-id' => $message->getHeader()->getFieldBody('Message-Id'),
      'raw' => $message->toString(),
      // @todo Add deliverer reference here, relevant if multiple present, https://www.drupal.org/node/2379909
    );

    Container::create(array(
      'origin_uri' => $origin_uri,
      'data' => array(
        // @todo Formally document this schema with present fields.
        'schema' => 'https://www.drupal.org/project/inmail/schema/message',
        'type' => 'application/json',
        'data' => json_encode($data),
      ),
      // @todo Call date accessor when/if it is implemented in https://www.drupal.org/node/2379923.
      'date' => strtotime($message->getHeader()->getFieldBody('Date')),
    ))->save();
  }
}
