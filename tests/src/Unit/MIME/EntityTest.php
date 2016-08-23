<?php

namespace Drupal\Tests\inmail\Unit\MIME;

use Drupal\inmail\MIME\Entity;
use Drupal\inmail\MIME\Header;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the Entity class.
 *
 * @coversDefaultClass \Drupal\inmail\MIME\Entity
 *
 * @group inmail
 */
class EntityTest extends UnitTestCase {

  /**
   * Tests the body accessors in context of decoding.
   *
   * @covers \Drupal\inmail\MIME\Entity::getDecodedBody
   *
   * @dataProvider stringsProvider
   */
  public function testGetDecodedBody(Header $header, $body, $decoded_body) {
    // Testing quoted-printable.
    $entity = new Entity($header, $body);
    $this->assertEquals($decoded_body, $entity->getDecodedBody());
  }

  /**
   * Tests the body accessor.
   *
   * @covers \Drupal\inmail\MIME\Entity::getBody
   *
   * @dataProvider stringsProvider
   */
  public function testGetBody(Header $header, $body) {
    $entity = new Entity($header, $body);
    $this->assertEquals($body, $entity->getBody());
  }

  /**
   * Data provider.
   *
   * @return array
   *   A list of triplets containing a header with encoding/charset fields, a
   *   body encoded accordingly, and the body un-encoded.
   */
  public function stringsProvider() {
    // Sample data to test UTF-8 and Base64 conversion and decoding.
    return [
      [
        new Header([
          ['name' => 'Content-Type', 'body' => 'text/plain; charset=UTF-8'],
          ['name' => 'Content-Transfer-Encoding', 'body' => 'quoted-printable'],
        ]),
        // Encoded body in UTF-8.
        '=E6=9C=A8',
        // Un-encoded body containing Chinese letter for English word 'wood'.
        '木',
      ],
      [
        new Header([
          ['name' => 'Content-Type', 'body' => 'text/plain; charset=UTF-8'],
          ['name' => 'Content-Transfer-Encoding', 'body' => 'base64'],
        ]),
        // Encoded body in Base64/quoted-printable format.
        'TGludXg',
        // Un-encoded body.
        'Linux',
      ],
      [
        new Header([
          ['name' => 'Content-Type', 'body' => 'text/plain; charset=UTF-8'],
          ['name' => 'Content-Transfer-Encoding', 'body' => 'binary'],
        ]),
        // Encoded body to test only domain of data
        // rather than reference to type of encoding.
        'Q',
        'Q',
      ],
      [
        new Header([
          ['name' => 'Content-Type', 'body' => 'text/plain; charset=UTF-8'],
          ['name' => 'Content-Transfer-Encoding', 'body' => 'quoted-printable'],
        ]),
        // Sample of invalid encoded UTF-8 body,
        // four octet sequence (in 3rd octet).
        '=f0=90=28=bc',
        // Tests validation and conversion to UTF-8.
        NULL,
      ],
    ];
  }

}
