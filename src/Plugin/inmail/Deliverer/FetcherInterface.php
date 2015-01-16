<?php
/**
 * @file
 * Contains \Drupal\inmail\Plugin\inmail\Deliverer\FetcherInterface.
 */

namespace Drupal\inmail\Plugin\inmail\Deliverer;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * A Fetcher is a Deliverer that can be executed.
 *
 * @ingroup deliverer
 */
interface FetcherInterface extends DelivererInterface, ConfigurablePluginInterface, PluginFormInterface {

  /**
   * Connects to the configured mailbox and fetches new mail.
   *
   * @return string[]
   *   The fetched messages, in complete raw form.
   */
  public function fetch();

  // @todo Add count() in https://www.drupal.org/node/2405047 or https://www.drupal.org/node/2399779
}
