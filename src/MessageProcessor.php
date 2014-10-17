<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageProcessor.
 */

namespace Drupal\bounce_processing;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Mail message processor using services to classify and handle messages.
 */
class MessageProcessor implements MessageProcessorInterface, ContainerInjectionInterface {

  /**
   * The message classifier to use.
   *
   * @var \Drupal\bounce_processing\MessageClassifierInterface
   */
  protected $classifier;

  /**
   * The handler to invoke for a classified message.
   *
   * @var \Drupal\bounce_processing\MessageHandlerInterface
   */
  protected $handler;

  /**
   * Constructs a new MessageProcesor.
   */
  public function __construct(MessageClassifierInterface $classifier, MessageHandlerInterface $handler) {
    // @todo Maybe make classifier and handler tagged services.
    $this->classifier = $classifier;
    $this->handler = $handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('bounce.classifier'), $container->get('bounce.handler'));
  }

  /**
   * {@inheritdoc}
   */
  public function process($raw) {
    $message = Message::parse($raw);
    $type = $this->classifier->classify($message);
    $this->handler->invoke($message, $type);
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
