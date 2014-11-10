<?php
/**
 * @file
 * Contains \Drupal\inmail\MessageProcessor.
 */

namespace Drupal\inmail;

use Drupal\inmail\MessageAnalyzer\MessageAnalyzerInterface;
use Drupal\inmail\MessageAnalyzer\Result\AnalyzerResult;
use Drupal\inmail\Plugin\inmail\Handler\HandlerInterface;

/**
 * Mail message processor using services to analyze and handle messages.
 */
class MessageProcessor implements MessageProcessorInterface {

  /**
   * A list of message analyzers to use.
   *
   * @var \Drupal\inmail\MessageAnalyzer\MessageAnalyzerInterface[]
   */
  protected $analyzers = array();

  /**
   * A list of handlers to invoke for an analyzed message.
   *
   * @var \Drupal\inmail\Plugin\inmail\Handler\HandlerInterface[]
   */
  protected $handlers = array();

  /**
   * Adds an analyzer object to the list of analyzer.
   *
   * @param \Drupal\inmail\MessageAnalyzer\MessageAnalyzerInterface $analyzer
   *   A message analyzer.
   * @param string $id
   *   The service id of the analyzer.
   */
  public function addAnalyzer(MessageAnalyzerInterface $analyzer, $id) {
    $this->analyzers[$id] = $analyzer;
  }

  /**
   * Removes an analyzer object from the list of analyzers.
   *
   * @param string $id
   *   The service id of the analyzer.
   */
  public function removeAnalyzer($id) {
    unset($this->analyzers[$id]);
  }

  // @todo Are these really useful outside testing with drush inmail-services?
  public function getAnalyzers() {
    return array_map(function($obj) {
      return get_class($obj);
    }, $this->analyzers);
  }

  public function getHandlers() {
    return array_map(function($obj) {
      return get_class($obj);
    }, $this->handlers);
  }

  /**
   * {@inheritdoc}
   */
  public function process($raw) {
    // Parse message.
    $message = Message::parse($raw);

    // Analyze message.
    $result = new AnalyzerResult();
    foreach ($this->analyzers as $analyzer) {
      $analyzer->analyze($message, $result);
    }

    // Handle message.
    // @todo Inject handler manager.
    /** @var \Drupal\inmail\HandlerManager $handler_manager */
    $handler_manager = \Drupal::service('plugin.manager.inmail.handler');
    foreach ($handler_manager->getHandlers() as $handler) {
      $handler->invoke($message, $result);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function processMultiple(array $messages) {
    foreach ($messages as $message) {
      $this->process($message);
    }
  }
}
