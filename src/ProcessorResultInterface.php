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
   * Returns an analyzer result instance, after first creating it if needed.
   *
   * If a result object has already been created with the given topic name, that
   * object will be used.
   *
   * @param string $topic
   *   An identifier for the analyzer result object.
   * @param callable $factory
   *   A function that returns an analyzer result object. This will be called if
   *   there is no object previously created for the given topic name.
   *
   * @return \Drupal\inmail\AnalyzerResultInterface
   *   The analyzer result object.
   *
   * @throws \InvalidArgumentException
   *   If the callable returns something else than an analyzer result object.
   */
  public function ensureAnalyzerResult($topic, callable $factory);

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
