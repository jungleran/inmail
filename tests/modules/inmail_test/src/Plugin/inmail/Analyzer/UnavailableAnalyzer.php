<?php

namespace Drupal\inmail_test\Plugin\inmail\Analyzer;

use Drupal\inmail\DefaultAnalyzerResult;
use Drupal\inmail\MIME\MessageInterface;
use Drupal\inmail\Plugin\inmail\Analyzer\AnalyzerBase;
use Drupal\inmail\ProcessorResultInterface;

/**
 * Provides an unavailable analyzer.
 *
 * @Analyzer(
 *   id = "unavailable_analyzer",
 *   label = @Translation("Unavailable Analyzer")
 * )
 */
class UnavailableAnalyzer extends AnalyzerBase {

  /**
   * {@inheritdoc}
   */
  public function analyze(MessageInterface $message, ProcessorResultInterface $processor_result) {
    /** @var \Drupal\inmail\DefaultAnalyzerResult $default_result */
    $default_result = $processor_result->getAnalyzerResult(DefaultAnalyzerResult::TOPIC);

    // Do the fake body update. This should not be executed as we only execute
    // available analyzers.
    $default_result->setBody('The body has been updated by UnavailableAnalyzer.');
  }

  /**
   * {@inheritdoc}
   */
  public static function checkPluginRequirements() {
    return [
      'title' => t('Unavailable Analyzer'),
      'description' => t('Unavailable Analyzer cannot be used.'),
      'severity' => REQUIREMENT_ERROR,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function checkInstanceRequirements() {
    return [
      'description' => $this->t('Wrong instance configuration.'),
      'severity' => REQUIREMENT_ERROR,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function isAvailable() {
    return FALSE;
  }

}
