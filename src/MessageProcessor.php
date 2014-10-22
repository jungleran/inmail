<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageProcessor.
 */

namespace Drupal\bounce_processing;

use Drupal\bounce_processing\MessageClassifier\MessageClassifierInterface;
use Drupal\bounce_processing\MessageHandler\MessageHandlerInterface;

/**
 * Mail message processor using services to classify and handle messages.
 */
class MessageProcessor implements MessageProcessorInterface {

  /**
   * A list of message classifiers to use.
   *
   * @var \Drupal\bounce_processing\MessageClassifier\MessageClassifierInterface[]
   */
  protected $classifiers = array();

  /**
   * A list of handlers to invoke for a classified message.
   *
   * @var \Drupal\bounce_processing\MessageHandler\MessageHandlerInterface[]
   */
  protected $handlers = array();

  /**
   * Adds a classifier object to the list of classifiers.
   *
   * @param \Drupal\bounce_processing\MessageClassifier\MessageClassifierInterface $classifier
   *   A message classifier.
   */
  public function addClassifier(MessageClassifierInterface $classifier) {
    $this->classifiers[] = $classifier;
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
  public function getClassifiers() {
    return array_map(function($obj) {
      return get_class($obj);
    }, $this->classifiers);
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

    // Classify message.
    $type = NULL;
    foreach ($this->classifiers as $classifier) {
      $type = $classifier->classify($message);
      if (isset($type)) {
        break;
      }
    }

    // Handle message.
    foreach ($this->handlers as $handler) {
      $handler->invoke($message, $type);
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
