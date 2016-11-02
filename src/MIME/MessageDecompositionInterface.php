<?php

namespace Drupal\inmail\MIME;

/**
 * Provides methods to decompose a message.
 *
 * @ingroup processing
 */
interface MessageDecompositionInterface {

  /**
   * Returns a flatten array of sub-entities.
   *
   * @param \Drupal\Inmail\MIME\EntityInterface $entity
   *   An entity to get sub-entities for.
   * @param string $current_path
   *   (optional) The current path. Defaults to empty string.
   *
   * @return \Drupal\Inmail\MIME\EntityInterface[]
   *   A flatten array of sub-entities separated by its paths.
   */
  public function getEntities(EntityInterface $entity, $current_path = '');

  /**
   * Returns a MIME entity for the given path.
   *
   * @param \Drupal\inmail\MIME\EntityInterface $entity
   *   The entity to resolve a path for.
   * @param string $path
   *   The entity path.
   *
   * @return \Drupal\inmail\MIME\EntityInterface|null
   *   Returns a MIME entity or null if it fails.
   */
  public function getEntityByPath(EntityInterface $entity, $path);

  /**
   * Returns a list of entities that match the given type.
   *
   * @param \Drupal\inmail\MIME\EntityInterface $entity
   *   The main entity.
   * @param string[] $types
   *   The list of types to get entities for.
   *
   * @return \Drupal\inmail\MIME\EntityInterface[]
   *   The list of matched entities keyed by the type name or
   *   an empty string if there is no match.
   */
  public function getEntitiesByType(EntityInterface $entity, array $types);

  /**
   * Returns an array with body paths.
   *
   * @param \Drupal\inmail\MIME\MessageInterface $message
   *   The message to get body paths for.
   *
   * @return array
   *   An array containing plain and HTML keys and its paths.
   */
  public function getBodyPaths(MessageInterface $message);

  /**
   * Builds an array of attachment properties.
   *
   * @param string $path
   *   The path to access the attachment.
   * @param \Drupal\inmail\MIME\EntityInterface $attachment
   *   The message part that should be displayed as an attachment.
   * @param \Drupal\Core\Url|null $download_url
   *   (optional) A download URL or null if it does not exist.
   *
   * @return array
   *   An array of attachment properties:
   *      - type: The attachment type (image, audio...)
   *      - content_type: The content type
   *      - filename: The file name or default name for unknown parts.
   *      - encoding: Content transfer encoding
   *      - content: Raw encoded content.
   *      - (optional) url: The URL to download the attachment.
   */
  public function buildAttachment($path, EntityInterface $attachment, $download_url = NULL);

}
