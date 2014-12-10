<?php
/**
 * @file
 * Contains \Drupal\inmail\MIME\Header.
 */

namespace Drupal\inmail\MIME;

/**
 * An abstraction of an email header.
 *
 * The header is defined in RFC 5322. In short, it consists of fields of the
 * form "Name: body", separated by newlines.
 *
 * Newlines may also occur within field bodies, but then only followed by space.
 * This allows fields to break across multiple lines, facilitating adherence to
 * the maximal line length of 78 recommended by the RFC.
 *
 * A header is allowed to have multiple fields with the same name, but the
 * accessors in this class only support finding the first occurrence (if any).
 *
 * @see http://tools.ietf.org/html/rfc5322#section-2.2
 * @see http://tools.ietf.org/html/rfc5322#section-3.6
 *
 * @ingroup mime
 */
class Header {

  /**
   * The fields constituting the header.
   *
   * @var array
   */
  protected $fields = array();

  /**
   * Creates a new Header object containing the optionally given fields.
   *
   * @param array $fields
   *   A list of fields, represented by arrays with string elements for the keys
   *   'name' and 'body'.
   */
  public function __construct($fields = array()) {
    foreach ($fields as $field) {
      if (isset($field['name']) && isset($field['body'])) {
        $this->addField($field['name'], $field['body'], FALSE);
      }
    }
  }

  /**
   * Returns the literal body of the first field with the given name.
   *
   * Some field names are allowed by the standards to occur more than once. This
   * accessor is however designed to only return the first occurrence (newest,
   * if fields are added to the top).
   *
   * @param string $name
   *   The name of a header field.
   *
   * @return null|string
   *   The literal body of the field, or NULL if the field is not present.
   */
  public function getFieldBodyUnfiltered($name) {
    $key = $this->findFirstField($name);
    if ($key !== FALSE) {
      return trim($this->fields[$key]['body']);
    }
    return NULL;
  }

  /**
   * Returns the body of the first field with the given name, without comments.
   *
   * Header bodies may contain comments in parentheses. This method works like
   * ::getFieldBodyUnfiltered(), but strips off such comments from the field
   * body before returning it.
   *
   * @param string $name
   *   The name of a header field.
   *
   * @return null|string
   *   The body of the field, without comments, or NULL if the field is not
   *   present.
   *
   * @see Header::getFieldBodyUnfiltered()
   */
  public function getFieldBody($name) {
    $body = $this->getFieldBodyUnfiltered($name);
    if (empty($body)) {
      return NULL;
    }
    // Filter out comments.
    $body = preg_replace('/\([^)]*\)/', '', $body);
    return $body;
  }

  /**
   * Adds a field to the header.
   *
   * Note that in the context of an MTA processing a message, headers are
   * usually added to the beginning rather than the end. It is up to the caller
   * of this method to ensure that added fields conform to standards as desired.
   *
   * @param string $name
   *   The name (key) of the field.
   * @param string $body
   *   The body (value) of the field.
   * @param bool $prepend
   *   If TRUE, the header is added to the beginning of the header, otherwise it
   *   is added to the end. Defaults to TRUE.
   */
  public function addField($name, $body, $prepend = TRUE) {
    if (!empty($name) && !empty($body)) {
      if ($prepend) {
        array_unshift($this->fields, ['name' => $name, 'body' => $body]);
      }
      else {
        $this->fields[] = ['name' => $name, 'body' => $body];
      }
    }
  }

  /**
   * Removes the first field with the given name.
   *
   * @param string $name
   *   The name of a field to remove. If no field with that name is present,
   *   nothing happens.
   *
   * @see Header::getFieldbodyUnfiltered()
   */
  public function removeField($name) {
    $key = $this->findFirstField($name);
    if ($key !== FALSE) {
      array_splice($this->fields, $key, 1);
    }
  }

  /**
   * Returns the index of the first field with the given name.
   *
   * Fields are iterated from top to bottom.
   *
   * @param string $name
   *   The name of the field to find.
   *
   * @return int|bool
   *   The index of the field in the internal field list, or FALSE if no field
   *   with that name is present.
   */
  protected function findFirstField($name) {
    // Iterate through headers and find the first match.
    foreach ($this->fields as $key => $field) {
      // Field name is case-insensitive.
      if (strcasecmp($field['name'], $name) == 0) {
        return $key;
      }
    }
    return FALSE;
  }

  /**
   * Concatenates the fields to a header string.
   *
   * @return string
   *   The header as a string, terminated by a newline.
   */
  public function toString() {
    // @todo Manage line length and inline encoding, https://www.drupal.org/node/2389327
    return implode("\n", array_map(function($field) {
      return "{$field['name']}: {$field['body']}";
    }, $this->fields)) . "\n";
  }

}
