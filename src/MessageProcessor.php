<?php
/**
 * @file
 * Contains \Drupal\inmail\MessageProcessor.
 */

namespace Drupal\inmail;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\inmail\MessageAnalyzer\MessageAnalyzerInterface;
use Drupal\inmail\MessageAnalyzer\Result\AnalyzerResult;

/**
 * Mail message processor using services to analyze and handle messages.
 *
 * @ingroup processing
 */
class MessageProcessor implements MessageProcessorInterface {

  /**
   * The storage for message analyzer configuration entities.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorage
   */
  protected $analyzerStorage;

  /**
   * The plugin manager for analyzer plugins.
   *
   * @var \Drupal\inmail\AnalyzerManagerInterface
   */
  protected $analyzerManager;

  /**
   * The storage for message handler configuration entities.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorage
   */
  protected $handlerStorage;

  /**
   * The plugin manager for handler plugins.
   *
   * @var \Drupal\inmail\HandlerManagerInterface
   */
  protected $handlerManager;

  /**
   * Constructs a new message processor.
   */
  public function __construct(EntityManagerInterface $entity_manager, AnalyzerManagerInterface $analyzer_manager, HandlerManagerInterface $handler_manager) {
    $this->analyzerStorage = $entity_manager->getStorage('inmail_analyzer');
    $this->analyzerManager = $analyzer_manager;
    $this->handlerStorage = $entity_manager->getStorage('inmail_handler');
    $this->handlerManager = $handler_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function process($raw) {
    // Parse message.
    $message = Message::parse($raw);

    // Analyze message.
    $result = new AnalyzerResult();
    $analyzer_configs = $this->analyzerStorage->loadMultiple();
    uasort($analyzer_configs, array($this->analyzerStorage->getEntityType()->getClass(), 'sort'));
    foreach ($analyzer_configs as $analyzer_config) {
      /** @var \Drupal\inmail\Entity\AnalyzerConfig $analyzer_config */
      if ($analyzer_config->status()) {
        $analyzer = $this->analyzerManager->createInstance($analyzer_config->getPluginId(), $analyzer_config->getConfiguration());
        $analyzer->analyze($message, $result);
      }
    }

    // Handle message.
    foreach ($this->handlerStorage->loadMultiple() as $handler_config) {
      /** @var \Drupal\inmail\Entity\HandlerConfig $handler_config */
      if ($handler_config->status()) {
        $handler = $this->handlerManager->createInstance($handler_config->getPluginId(), $handler_config->getConfiguration());
        $handler->invoke($message, $result);
      }
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
