<?php

namespace Drupal\inmail_test\Plugin\inmail\Analyzer;

use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\inmail\DefaultAnalyzerResult;
use Drupal\inmail\MIME\MessageInterface;
use Drupal\inmail\Plugin\inmail\Analyzer\AnalyzerBase;
use Drupal\inmail\ProcessorResultInterface;

/**
 * Provides a test analyzer.
 *
 * @Analyzer(
 *   id = "test_analyzer",
 *   label = @Translation("Test Analyzer")
 * )
 */
class TestAnalyzer extends AnalyzerBase {

  /**
   * {@inheritdoc}
   */
  public function analyze(MessageInterface $message, ProcessorResultInterface $processor_result) {
    /** @var \Drupal\inmail\DefaultAnalyzerResult $default_result */
    $default_result = $processor_result->getAnalyzerResult(DefaultAnalyzerResult::TOPIC);

    // Provide sample context.
    $this->addContext($default_result);
  }

  /**
   * Adds a sample context.
   *
   * @param \Drupal\inmail\DefaultAnalyzerResult $default_result
   */
  protected function addContext($default_result) {
    $context_definition = new ContextDefinition('string', $this->t('Test Context'));
    $context = new Context($context_definition, 'Sample context value');
    $default_result->addContext('test', $context);
  }

}
