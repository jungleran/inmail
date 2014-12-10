<?php
/**
 * @file
 * Contains \Drupal\inmail\MIME\Parser.
 */

namespace Drupal\inmail\MIME;

/**
 * Parser for MIME (email) messages.
 *
 * The newline sequence used in MIME is CRLF ('\r\n'). To simplify processing,
 * however, the raw input is immediately converted to LF ('\n'). For example,
 * messages saved on the filesystem are likely to use LF. In other words, the
 * input can use either CRLF or LF, but output and API will use LF.
 *
 * @todo Throw MIMEParserException, https://www.drupal.org/node/2389807
 *
 * @ingroup mime
 */
class Parser implements ParserInterface {

  /**
   * {@inheritdoc}
   */
  public function parse($raw) {
    // Normalize to LF.
    $raw = str_replace("\r\n", "\n", $raw);

    // Header is separated from body by a blank line.
    list($header, $body) = preg_split("/(^|\n)\n/", $raw, 2);
    $header = $this->parseHeaderFields($header);

    // @todo Decode base64 body, https://www.drupal.org/node/2381881
    $entity = new Entity($header, $body);

    // Identify a multipart entity and decorate the entity object.
    if ($this->isMultipart($entity)) {
      $parts = $this->extractMultipartParts($entity);
      $entity = new MultipartEntity($entity, $parts);

      // Identify a DSN message and decorate the entity object further.
      // Specified in RFC 3464, a DSN has content type "multipart/report" with
      // report-type "delivery-status". Those values are case-insensitive.
      $content_type = $entity->getContentType();
      // @todo figure out if types need to be pluggable to support more.
      if (strcasecmp($content_type['subtype'], 'report') == 0 && strcasecmp($content_type['parameters']['report-type'], 'delivery-status') == 0) {
        // Parse the second part, which contains groups of fields having the
        // same syntax as header fields.
        $dsn_fields = array();
        $body = trim($entity->getPart(1)->getBody());
        foreach (explode("\n\n", $body) as $field_group) {
          $dsn_fields[] = $this->parseHeaderFields($field_group);
        }
        $entity = new DSNEntity($entity, $dsn_fields);
      }
    }

    return $entity;
  }

  /**
   * Checks if the entity is of content type "multipart".
   *
   * @param \Drupal\inmail\MIME\EntityInterface $entity
   *   The entity to check.
   *
   * @return bool
   *   TRUE if the entity has type "multipart", otherwise FALSE.
   */
  protected function isMultipart(EntityInterface $entity) {
    $content_type = $entity->getContentType();
    return strtolower($content_type['type']) == 'multipart' && isset($content_type['parameters']['boundary']);
  }

  /**
   * Parses the body of a multipart entity into parts.
   *
   * This method must only be called if ::isMultipart() returns TRUE.
   *
   * The Multipart content type has a required 'boundary' parameter. The
   * boundary is used to separate the constituting parts in the body of the
   * entity.
   *
   * Each part is in turn parsed as an entity.
   *
   * @param \Drupal\inmail\MIME\EntityInterface $entity
   *   The entity to interpret as multipart.
   *
   * @return \Drupal\inmail\MIME\EntityInterface[]
   *   The constituting parts of the multipart message.
   */
  protected function extractMultipartParts(EntityInterface $entity) {
    // Identify the boundary string.
    $content_type = $entity->getContentType();
    $boundary = $content_type['parameters']['boundary'];

    // The last part is terminated by "--$boundary--".
    $parts = strstr($entity->getBody(), "\n--$boundary--", TRUE);
    // Prepend with newline to facilitate explosion.
    $parts = "\n$parts";
    // The parts are separated by "--$boundary".
    $parts = explode("\n--$boundary\n", $parts);
    // The content before the first part is to be ignored.
    array_shift($parts);

    // Recursively parse each part.
    foreach ($parts as $key => $part) {
      $parts[$key] = $this->parse($part);
    }
    return $parts;
  }

  /**
   * Parses a string header into a Header object.
   *
   * Header fields are separated by newlines followed by non-whitespace. If a
   * line begins with space, it is part of the previous header field.
   *
   * Passing an empty string is allowed, and results in an empty Header object.
   *
   * @param string $raw_header
   *   A string in the header format defined by RFC 2822 "Internet Message
   *   Format".
   *
   * @return \Drupal\inmail\MIME\Header
   *   The resulting Header object abstraction.
   *
   * @see https://tools.ietf.org/html/rfc2822#section-2.2
   */
  protected function parseHeaderFields($raw_header) {
    $header = new Header();

    // In some entities, headers are optional.
    if (empty($raw_header)) {
      return $header;
    }

    // Header fields are separated by CRLF followed by non-whitespace.
    $fields = preg_split('/\n(?!\s)/', $raw_header);
    foreach ($fields as $field) {

      list($name, $body) = explode(':', $field, 2);
      // @todo Manage inline encoding, https://www.drupal.org/node/2389327
      $header->addField(trim($name), trim($body), FALSE);
    }
    return $header;
  }

}
