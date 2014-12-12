<?php
/**
 * @file
 * Contains \Drupal\inmail\MIME\ParserInterface.
 */

namespace Drupal\inmail\MIME;

/**
 * Defines methods for a parser of MIME (email) messages.
 *
 * The MIME standards define an email message more generally as an "entity". An
 * MIME entity consists of a header and a body. The header in turn is a list of
 * header fields. The type of the body is defined by the Content-Type header
 * field. By default it is 7bit ASCII text.
 *
 * @ingroup mime
 */
interface ParserInterface {

  /**
   * Parses a string entity (message) into an Entity object.
   *
   * The input can be a message or more generally a MIME entity.
   *
   * While the header section is required in a message, it is optional for
   * multipart parts, in which case the entity contains only the body, preceded
   * by a double CRLF.
   *
   * @param string $raw
   *   A string entity.
   *
   * @return \Drupal\inmail\MIME\EntityInterface
   *   The resulting Entity object abstraction.
   *
   * @throws \Drupal\inmail\MIME\ParseException
   *   If parsing fails.
   */
  public function parse($raw);

}
