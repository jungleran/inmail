<?php
/**
 * @file
 * Contains \Drupal\inmail\Annotation\Handler.
 */

namespace Drupal\inmail\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines the plugin annotation of message handlers.
 *
 * @ingroup handler
 *
 * @Annotation
 */
class MessageHandler extends Plugin {

  /**
   * A short machine-name to uniquely identify the handler.
   *
   * @var string
   */
  protected $id;

}
