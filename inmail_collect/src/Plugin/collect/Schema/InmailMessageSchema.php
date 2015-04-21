<?php
/**
 * @file
 * Contains \Drupal\inmail_collect\Plugin\collect\Schema\InmailMessageSchema.
 */

namespace Drupal\inmail_collect\Plugin\collect\Schema;

use Drupal\collect\CollectContainerInterface;
use Drupal\collect\Schema\PropertyDefinition;
use Drupal\collect\Schema\SchemaBase;
use Drupal\collect\Schema\SpecializedDisplaySchemaInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\inmail\MIME\Parser;
use Drupal\inmail\MIME\Renderer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Schema plugin for Inmail messages.
 *
 * @Schema(
 *   id = "inmail_message",
 *   label = @Translation("Email message")
 * )
 */
class InmailMessageSchema extends SchemaBase implements ContainerFactoryPluginInterface, SpecializedDisplaySchemaInterface {

  /**
   * The injected MIME parser.
   *
   * @var \Drupal\inmail\MIME\Parser
   */
  protected $parser;

  /**
   * The injected MIME renderer.
   *
   * @var \Drupal\inmail\MIME\Renderer
   */
  protected $renderer;

  /**
   * Constructs a new InmailMessageSchema plugin instance.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Parser $parser, Renderer $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->parser = $parser;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('inmail.mime_parser'),
      $container->get('inmail.mime_renderer')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function parse(CollectContainerInterface $container) {
    $raw = json_decode($container->getData())->raw;
    return $this->parser->parseMessage($raw);
  }

  /**
   * {@inheritdoc}
   */
  public function build($data, CollectContainerInterface $container) {
    /** @var \Drupal\inmail\MIME\EntityInterface $data */
    return $this->renderer->renderEntity($data);
  }

  /**
   * {@inheritdoc}
   */
  public function buildTeaser($data) {
    /** @var \Drupal\inmail\MIME\EntityInterface $data */
    $output = array();

    $output['subject'] = array(
      '#type' => 'item',
      '#title' => $this->t('Subject'),
      '#markup' => htmlentities($data->getHeader()->getFieldBody('Subject')),
    );
    $output['from'] = array(
      '#type' => 'item',
      '#title' => $this->t('From'),
      '#markup' => htmlentities($data->getHeader()->getFieldBody('From')),
    );
    $output['to'] = array(
      '#type' => 'item',
      '#title' => $this->t('To'),
      '#markup' => htmlentities($data->getHeader()->getFieldBody('To')),
    );

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public static function getStaticPropertyDefinitions() {
    $properties['body'] = new PropertyDefinition('body', DataDefinition::create('string')
      ->setLabel(t('Body')));

    $properties['subject'] = new PropertyDefinition('subject', DataDefinition::create('string')
      ->setLabel(t('Subject')));

    $properties['to'] = new PropertyDefinition('to', DataDefinition::create('string')
      ->setLabel(t('To')));

    $properties['to_address'] = new PropertyDefinition('to_address', DataDefinition::create('email')
      ->setLabel(t('To address')));

    $properties['from'] = new PropertyDefinition('from', DataDefinition::create('string')
      ->setLabel(t('From')));

    $properties['from_address'] = new PropertyDefinition('from_address', DataDefinition::create('email')
      ->setLabel(t('From address')));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate($data, $property_name) {
    // @todo Define a query format. For To/From/Cc, allow it to specify name, address or both.
    /** @var \Drupal\inmail\MIME\EntityInterface $message */
    $message = $data;

    if ($property_name == 'body') {
      // @todo Handle MultipartEntity, https://www.drupal.org/node/2450229
      return $message->getDecodedBody();
    }
    if (in_array($property_name, ['to_address', 'from_address'])) {
      $field_name = substr($property_name, 0, strpos($property_name, '_'));
      $field_body = $message->getHeader()->getFieldBody($field_name);
      $emails = Parser::parseAddress($field_body);
      // @todo Return list of all addresses, https://www.drupal.org/node/2379801
      return reset($emails);
    }
    // Many property names are just header field names.
    return $message->getHeader()->getFieldBody($property_name);
  }

}