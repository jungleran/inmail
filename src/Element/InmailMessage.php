<?php

namespace Drupal\inmail\Element;

use Drupal\Core\Render\Element\RenderElement;
use Drupal\inmail\MIME\MultipartEntity;
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
    if ($element['#message'] && $element['#message'] instanceof MultipartMessage) {
      $multipart_message = $element['#message'];
      $view_mode = $element['#view_mode'];
      $element = [
        'multipart_message' => [
          '#theme' => 'inmail_multipart_message',
          '#message' => $multipart_message,
          '#view_mode' => $view_mode,
        ],
        'multipart_message_parts' => static::getRenderableParts($multipart_message, $view_mode),
      ];
    }

    return $element;
  }

  /**
   * Returns a renderable array of MIME entities.
   *
   * @param \Drupal\inmail\MIME\MultipartEntity $multipart_entity
   *   The multipart entity.
   * @param string $view_mode
   *   The view mode to render the message part.
   *
   * @return array
   *   An renderable array of MIME entities.
   */
  public static function getRenderableParts(MultipartEntity $multipart_entity, $view_mode) {
    $elements = [];

    // Iterate over message parts.
    foreach ($multipart_entity->getParts() as $message_part) {
      // In case the message part is a multipart entity, recurse until we get
      // a non-multipart entity.
      if ($message_part instanceof MultipartEntity) {
        $elements = array_merge($elements, static::getRenderableParts($message_part, $view_mode));
      }
      else {
        // Otherwise, build an array to render MIME entity.
        $elements[] = [
          '#type' => 'fieldset',
          '#title' => $message_part->getType(),
          'message_part' => [
            '#theme' => 'inmail_message',
            '#message' => $message_part,
            '#view_mode' => $view_mode,
          ],
        ];
      }
    }

    return $elements;
  }

}
