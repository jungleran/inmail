<?php
/**
 * @file
 * Contains \Drupal\Tests\bounce_processing\Unit\BounceProcessingUnitTestBase.
 */

namespace Drupal\Tests\bounce_processing\Unit;

use Drupal\Tests\UnitTestCase;

/**
 * Base class for Bounce Processing unit tests.
 */
class BounceProcessingUnitTestBase extends UnitTestCase {

  /**
   * Returns the raw contents of a given test message file.
   *
   * @param string $filename
   *   The name of the file.
   *
   * @return string
   *   The message content.
   */
  protected function getRaw($filename) {
    $path = __DIR__ . '/../../modules/bounce_processing_test/eml/' . $filename;
    return file_get_contents($path);
  }

}
