<?php

namespace Drupal\Tests\inmail\Unit\MIME;

use Drupal\inmail\MIME\MimeEntity;
use Drupal\inmail\MIME\MimeHeader;
use Drupal\Tests\UnitTestCase;

/**
 * Tests the Entity class.
 *
 * @coversDefaultClass \Drupal\inmail\MIME\MimeEntity
 *
 * @group inmail
 */
class MimeEntityTest extends UnitTestCase {

  /**
   * Tests the body accessors in context of decoding.
   *
   * @covers \Drupal\inmail\MIME\MimeEntity::getDecodedBody
   *
   * @dataProvider stringsProvider
   */
  public function testGetDecodedBody(MimeHeader $header, $body, $decoded_body) {
    // Testing quoted-printable.
    $entity = new MimeEntity($header, $body);
    $this->assertEquals($decoded_body, $entity->getDecodedBody());
  }

  /**
   * Tests the body accessor.
   *
   * @covers \Drupal\inmail\MIME\MimeEntity::getBody
   *
   * @dataProvider stringsProvider
   */
  public function testGetBody(MimeHeader $header, $body) {
    $entity = new MimeEntity($header, $body);
    $this->assertEquals($body, $entity->getBody());
  }

  /**
   * Tests the header accessor.
   *
   * @covers \Drupal\inmail\MIME\MimeEntity::getHeader
   *
   * @dataProvider stringsProvider
   */
  public function testGetHeader(MimeHeader $header, $body) {
    $entity = new MimeEntity($header, $body);
    $this->assertEquals($header, $entity->getHeader());
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
        new MimeHeader([
          ['name' => 'Content-Type', 'body' => 'text/plain; charset=UTF-8'],
          ['name' => 'Content-Transfer-Encoding', 'body' => 'quoted-printable'],
        ]),
        // Encoded body in UTF-8.
        '=E6=9C=A8',
        // Un-encoded body containing Chinese letter for English word 'wood'.
        '木',
      ],
      [
        new MimeHeader([
          ['name' => 'Content-Type', 'body' => 'text/plain; charset=UTF-8'],
          ['name' => 'Content-Transfer-Encoding', 'body' => 'base64'],
        ]),
        // Encoded body in Base64/quoted-printable format.
        'TGludXg',
        // Un-encoded body.
        'Linux',
      ],
      [
        new MimeHeader([
          ['name' => 'Content-Type', 'body' => 'text/plain; charset=UTF-8'],
          ['name' => 'Content-Transfer-Encoding', 'body' => 'binary'],
        ]),
        // Encoded body to test only domain of data
        // rather than reference to type of encoding.
        'Q',
        'Q',
      ],
      [
        new MimeHeader([
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

  /**
   * Data provider.
   *
   * @return array
   *   A list of triplets containing MimeHeader with Content-Type field, and
   *   expected content-type and charset.
   */
  public function contentTypeProvider() {
    // Sample data to test content-type extraction.
    return [
      [
        new MimeHeader([
          ['name' => 'Content-Type', 'body' => 'text/plain; charset=UTF-8'],
        ]),
        // Expected content-type.
        'text/plain',
        // Expected charset.
        'UTF-8',
      ],
      [
        new MimeHeader([
          ['name' => 'Content-Type', 'body' => 'text/html; charset=ASCII'],
        ]),
        'text/html',
        'ASCII',
      ],
      [
        new MimeHeader([
          ['name' => 'Content-Type', 'body' => 'multipart/alternative; charset=UTF-32'],
        ]),
        'multipart/alternative',
        'UTF-32',
      ],
    ];
  }

  /**
   * Tests joining header with body.
   *
   * @covers \Drupal\inmail\MIME\MimeEntity::toString
   */
  public function testToString() {
    $entity = new MimeEntity(new MimeHeader([[
      'name' => 'Subject', 'body' => 'Foo Bar',
    ]]), 'When I joined them, foobar was already being commonly used as a throw-away file name.');
    $this->assertEquals($entity->toString(), "Subject: Foo Bar\n\nWhen I joined them, foobar was already being commonly used as a throw-away file name.");
  }

  /**
   * Tests content type accessor.
   *
   * @covers \Drupal\inmail\MIME\MimeEntity::getContentType
   *
   * @dataProvider contentTypeProvider
   */
  public function testGetContentType(MimeHeader $header, $content, $charset) {
    $entity = new MimeEntity($header, 'MimeMessage Body');
    $content_type = $entity->getContentType();
    $char_set = $content_type['parameters']['charset'];
    $this->assertEquals($content, $content_type['type'] . '/' . $content_type['subtype']);
    $this->assertEquals($charset, $char_set);
  }

  /**
   * Tests the body encoding.
   *
   * @covers \Drupal\inmail\MIME\MimeEntity::getContentTransferEncoding
   */
  public function testGetContentTransferEncoding() {
    $entity = new MimeEntity(new MimeHeader([
      ['name' => 'Content-Transfer-Encoding', 'body' => 'base64'],
    ]), 'Hello World');
    $this->assertEquals('base64', $entity->getContentTransferEncoding());
  }

}
