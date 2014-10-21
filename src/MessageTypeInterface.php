<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageTypeInterface.
 */

namespace Drupal\bounce_processing;

/**
 * A container of message classification results.
 */
interface MessageTypeInterface {

  /**
   * Returns a human-readable short description of the type.
   *
   * @return string
   *   A short description of the type.
   */
  public function getLabel();

}
