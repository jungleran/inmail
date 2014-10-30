<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageProcessor.
 */

namespace Drupal\bounce_processing;

use Drupal\bounce_processing\MessageAnalyzer\MessageAnalyzerInterface;
use Drupal\bounce_processing\MessageHandler\MessageHandlerInterface;

/**
 * Mail message processor using services to analyze and handle messages.
 */
class MessageProcessor implements MessageProcessorInterface {

  /**
   * A list of message analyzers to use.
   *
   * @var \Drupal\bounce_processing\MessageAnalyzer\MessageAnalyzerInterface[]
   */
  protected $analyzers = array();

  /**
   * A list of handlers to invoke for an analyzed message.
   *
   * @var \Drupal\bounce_processing\MessageHandler\MessageHandlerInterface[]
   */
  protected $handlers = array();

  /**
   * Adds an analyzer object to the list of analyzer.
   *
   * @param \Drupal\bounce_processing\MessageAnalyzer\MessageAnalyzerInterface $analyzer
   *   A message analyzer.
   */
  public function addAnalyzer(MessageAnalyzerInterface $analyzer) {
    $this->analyzers[] = $analyzer;
  }

  /**
   * Adds a handler object to the list of handlers.
   *
   * @param \Drupal\bounce_processing\MessageHandler\MessageHandlerInterface $handler
   *   A message handler.
   */
  public function addHandler(MessageHandlerInterface $handler) {
    $this->handlers[] = $handler;
  }

  // @todo Are these really useful outside testing with drush bounce-services?
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
