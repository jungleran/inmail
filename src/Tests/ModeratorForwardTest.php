<?php
/**
 * @file
 * Contains \Drupal\inmail\Tests\ModeratorForwardTest.
 */

namespace Drupal\inmail\Tests;

use Drupal\simpletest\KernelTestBase;

/**
 * Tests the Moderator Forward handler plugin.
 *
 * @group inmail
 */
class ModeratorForwardTest extends KernelTestBase {

  /**
   * Tests the Moderator Forward handler plugin.
   */
  public function testModeratorForward() {
    $this->fail('@todo');

    // Do not handle if message is bounce.

    // Do not handle if moderator address is unset.

    // Do not handle, and log an error, if moderator address is same as intended
    // recipient.

    // Do not handle, and log an error, if the custom X header is set.

    // Forward non-bounces if conditions are right.
  }

}
