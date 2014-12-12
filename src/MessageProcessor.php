<?php
/**
 * @file
 * Contains \Drupal\inmail\MessageProcessor.
 */

namespace Drupal\inmail;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\inmail\MIME\ParseException;

/**
 * Mail message processor using services to analyze and handle messages.
 *
 * @todo Fetch email by IMAP/POP3 https://www.drupal.org/node/2379889
 * @todo Evaluate the analysis algorithms in D7 Bounce and CiviCRM https://www.drupal.org/node/2379845
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
   * The injected logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $loggerChannel;

  /**
   * Constructs a new message processor.
   */
  public function __construct(EntityManagerInterface $entity_manager, AnalyzerManagerInterface $analyzer_manager, HandlerManagerInterface $handler_manager, LoggerChannelInterface $logger_channel) {
    $this->analyzerStorage = $entity_manager->getStorage('inmail_analyzer');
    $this->analyzerManager = $analyzer_manager;
    $this->handlerStorage = $entity_manager->getStorage('inmail_handler');
    $this->handlerManager = $handler_manager;
    $this->loggerChannel = $logger_channel;
  }

  /**
   * {@inheritdoc}
   */
  public function process($raw) {
    // @todo Fetchers should identify themselves https://www.drupal.org/node/2379909

    // Parse message.
    /** @var \Drupal\inmail\MIME\ParserInterface $parser */
    $parser = \Drupal::service('inmail.mime_parser');
    try {
      $message = $parser->parse($raw);
    }
    catch (ParseException $e) {
      $this->loggerChannel->info('Unable to process message, parser failed with message "@message"', array('@message' => $e->getMessage()));
      return;
    }

    // Analyze message.
    $result = new ProcessorResult();
    $analyzer_configs = $this->analyzerStorage->loadMultiple();
    uasort($analyzer_configs, array($this->analyzerStorage->getEntityType()->getClass(), 'sort'));
    foreach ($analyzer_configs as $analyzer_config) {
      /** @var \Drupal\inmail\Entity\AnalyzerConfig $analyzer_config */
      if ($analyzer_config->status()) {
        /** @var \Drupal\inmail\Plugin\inmail\Analyzer\AnalyzerInterface $analyzer */
        $analyzer = $this->analyzerManager->createInstance($analyzer_config->getPluginId(), $analyzer_config->getConfiguration());
        $analyzer->analyze($message, $result);
      }
    }

    // Handle message.
    // @todo Collect handler results https://www.drupal.org/node/2379941
    foreach ($this->handlerStorage->loadMultiple() as $handler_config) {
      /** @var \Drupal\inmail\Entity\HandlerConfig $handler_config */
      if ($handler_config->status()) {
        /** @var \Drupal\inmail\Plugin\inmail\handler\HandlerInterface $handler */
        $handler = $this->handlerManager->createInstance($handler_config->getPluginId(), $handler_config->getConfiguration());
        $handler->invoke($message, $result);
      }
    }

    // @todo Log incoming messages and actions taken https://www.drupal.org/node/2380965
    //   Log all the messages in $result (and AnalyzerResult objects) in the
    //   context of Message-Id.
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
