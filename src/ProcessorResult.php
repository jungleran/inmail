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
  protected $analyzerResults;

  /**
   * {@inheritdoc}
   */
  public function addAnalyzerResult($topic, AnalyzerResultInterface $analyzer_result) {
    if (!isset($this->analyzerResults[$topic])) {
      $this->analyzerResults[$topic] = $analyzer_result;
    }
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

}
