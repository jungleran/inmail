<?php
/**
 * @file
 * Contains \Drupal\inmail_collect\Plugin\collect\Schema\InmailMessageSchema.
 */

namespace Drupal\inmail_collect\Plugin\collect\Schema;

use Drupal\collect\Plugin\Field\FieldType\CollectDataItem;
use Drupal\collect\Schema\SchemaBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
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
class InmailMessageSchema extends SchemaBase implements ContainerFactoryPluginInterface {

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
  public function parse(CollectDataItem $data_field) {
    $raw = json_decode($data_field->data)->raw;
    return $this->parser->parseMessage($raw);
  }

  /**
   * {@inheritdoc}
   */
  public function build($data) {
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
  public function getPropertyDefinitions() {
    $properties['body'] = DataDefinition::create('string_long')
      ->setLabel(t('Body'));

    $properties['subject'] = DataDefinition::create('string')
      ->setLabel(t('Subject'));

    $properties['to'] = DataDefinition::create('string')
      ->setLabel(t('To'));

    $properties['from'] = DataDefinition::create('string')
      ->setLabel(t('From'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function getDataProperty(ComplexDataInterface $data, $property_name) {
    /** @var \Drupal\inmail\MIME\EntityInterface $message */
    $message = $data->getValue();

    if ($property_name == 'body') {
      // @todo Handle MultipartEntity.
      $value = $message->getDecodedBody();
    }
    else {
      $value = $message->getHeader()->getFieldBody($property_name);
    }

    // Using this method creates the typed data as a property of $data.
    // @todo Inject TypedDataManager.
    return \Drupal::typedDataManager()->getPropertyInstance($data, $property_name, $value);
  }

}
