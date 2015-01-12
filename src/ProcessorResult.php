<?php
/**
 * @file
 * Contains \Drupal\inmail\ProcessorResult.
 */

namespace Drupal\inmail;

/**
 * The processor result collects outcomes of a single mail processing pass.
 *
 * @ingroup processing
 */
class ProcessorResult implements ProcessorResultInterface {

  /**
   * Instantiated analyzer result objects, keyed by topic.
   *
   * @var \Drupal\inmail\AnalyzerResultInterface[]
   */
  protected $analyzerResults = array();

  /**
   * Logged messages.
   *
   * @var array[][]
   */
  protected $log = array();

  /**
   * {@inheritdoc}
   */
  public function ensureAnalyzerResult($topic, callable $factory) {
    // Create the result object if it does not exist.
    if (!isset($this->analyzerResults[$topic])) {
      $analyzer_result = $factory();
      if (!$analyzer_result instanceof AnalyzerResultInterface) {
        throw new \InvalidArgumentException('Factory callable did not return an AnalyzerResultInterface instance');
      }
      $this->analyzerResults[$topic] = $analyzer_result;
    }

    // Return the result object.
    return $this->analyzerResults[$topic];
  }

  /**
   * {@inheritdoc}
   */
  public function getAnalyzerResult($topic) {
    if (isset($this->analyzerResults[$topic])) {
      return $this->analyzerResults[$topic];
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getAnalyzerResults() {
    return $this->analyzerResults;
  }

  /**
   * {@inheritdoc}
   */
  public function log($source, $message, array $placeholders = array()) {
    $this->log[$source][] = [
      'message' => $message,
      'placeholders' => $placeholders,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function readLog() {
    return $this->log;
  }

}