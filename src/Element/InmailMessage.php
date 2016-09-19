<?php

namespace Drupal\inmail\Element;

use Drupal\Core\Render\Element\RenderElement;
use Drupal\inmail\MIME\MultipartMessage;

/**
 * Provides a render element for displaying Inmail Message.
 *
 * Properties:
 * - #message: The parsed message object.
 *    An instance of \Drupal\inmail\MIME\MessageInterface.
 * - #view_mode: The view mode ("teaser" or "full").
 *
 * Usage example:
 * @code
 * $build['inmail_message_example'] = [
 *   '#title' => $this->t('Inmail Message Example'),
 *   '#type' => 'inmail_message',
 *   '#message' => $message,
 *   '#view_mode' => 'full',
 * ];
 * @endcode
 *
 * @RenderElement("inmail_message")
 */
class InmailMessage extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return [
      '#theme' => 'inmail_message',
      '#pre_render' => [
        [static::class, 'preRenderMessage'],
      ],
    ];
  }

  /**
   * Pre-render callback.
   *
   * @param array $element
   *   A structured array:
   *   - #message: The parsed message object.
   *   - #view_mode: The view mode ("teaser" or "full").
   *
   * @return array
   *   The passed-in element.
   */
  public static function preRenderMessage($element) {
    if ($message = $element['#message']) {
      // Use different template for multipart messages.
      if ($message instanceof MultipartMessage) {
        $element['#theme'] = 'inmail_multipart_message';
      }
    }

    return $element;
  }

}
