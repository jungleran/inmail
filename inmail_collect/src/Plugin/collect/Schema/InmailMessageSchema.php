<?php
/**
 * @file
 * Contains \Drupal\inmail_collect\Plugin\collect\Schema\InmailMessageSchema.
 */

namespace Drupal\inmail_collect\Plugin\collect\Schema;

use Drupal\collect\Entity\Container;
use Drupal\collect\Plugin\collect\Schema\SchemaInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\inmail\MIME\ParseException;
use Drupal\inmail\MIME\Parser;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Schema plugin for Inmail messages.
 *
 * @Schema("https://www.drupal.org/project/inmail/schema/message")
 */
class InmailMessageSchema extends PluginBase implements SchemaInterface, ContainerFactoryPluginInterface {

  /**
   * The injected MIME parser.
   *
   * @var \Drupal\inmail\MIME\Parser
   */
  protected $parser;

  /**
   * Constructs a new InmailMessageSchema plugin instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Parser $parser) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->parser = $parser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('inmail.mime_parser')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render(Container $container) {
    $entity = NULL;
    try {
      $entity = $this->parseContainer($container);
    }
    catch (ParseException $exception) {
      return array('#markup' => $this->t('Message could not be parsed.'));
    }

    // @todo Improve
    // @todo Templatify
    $output = array();
    $output['body'] = array(
      '#markup' => $this->renderPlainText($container),
    );

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function renderPlainText(Container $container) {
    try {
      // @todo Improve
      return strip_tags(html_entity_decode($this->parseContainer($container)->getDecodedBody()));
    }
    catch (ParseException $exception) {
      return NULL;
    }
  }

  /**
   * @param \Drupal\collect\Entity\Container $container
   *
   * @return \Drupal\inmail\MIME\EntityInterface
   *   The parsed entity.
   *
   * @throws \Drupal\inmail\MIME\ParseException
   *   If parsing failed.
   */
  protected function parseContainer(Container $container) {
    $data = json_decode($container->getData());
    $raw = $data->raw;
    return $this->parser->parse($raw);
  }

}
