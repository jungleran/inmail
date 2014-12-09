<?php
/**
 * @file
 * Contains \Drupal\inmail\ProcessorResultInterface.
 */

namespace Drupal\inmail;

/**
 * The processor result collects outcomes of a single mail processing pass.
 *
 * @ingroup processing
 */
interface ProcessorResultInterface {

  /**
   * Adds another analyzer result.
   *
   * The added analyzer result must be new and unmodified. If an analyzer result
   * has already been added for the same topic, nothing happens.
   *
   * @param string $topic
   *   An identifier for the new analyzer result object.
   * @param \Drupal\inmail\AnalyzerResultInterface $analyzer_result
   *   The new analyzer result object.
   *
   * @todo Change to ensureAnalyzerResult($topic, callable $factory), https://www.drupal.org/node/2389875
   */
  public function addAnalyzerResult($topic, AnalyzerResultInterface $analyzer_result);

  /**
   * Returns an analyzer result instance.
   *
   * @param string $topic
   *   The identifier for the analyzer result object.
   *
   * @return \Drupal\inmail\AnalyzerResultInterface
   *   The analyzer result object. If no result object has yet been added for
   *   the given key, this returns NULL.
   */
  public function getAnalyzerResult($topic);

}
