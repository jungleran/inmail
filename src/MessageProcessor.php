<?php
/**
 * @file
 * Contains \Drupal\inmail\MessageProcessor.
 */

namespace Drupal\inmail;

use Drupal\inmail\MessageAnalyzer\MessageAnalyzerInterface;
use Drupal\inmail\MessageHandler\MessageHandlerInterface;

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
   * @var \Drupal\inmail\MessageHandler\MessageHandlerInterface[]
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

  /**
   * Adds a handler object to the list of handlers.
   *
   * @param \Drupal\inmail\MessageHandler\MessageHandlerInterface $handler
   *   A message handler.
   * @param string $id
   *   The service id of the handler.
   */
  public function addHandler(MessageHandlerInterface $handler, $id) {
    $this->handlers[$id] = $handler;
  }

  /**
   * Removes a handler object from the list of handlers.
   *
   * @param string $id
   *   The service id of the handler.
   */
  public function removeHandler($id) {
    unset($this->handlers[$id]);
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

    // @todo Pass write-only to Analyzers, pass read-only to Handlers.
    // Analyze message.
    $result = new AnalyzerResult();
    foreach ($this->analyzers as $analyzer) {
      $analyzer->analyze($message, $result);
    }

    // Handle message.
    foreach ($this->handlers as $handler) {
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
