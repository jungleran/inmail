<?php
/**
 * @file
 * Contains \Drupal\inmail_collect\Plugin\collect\Schema\InmailMessageSchema.
 */

namespace Drupal\inmail_collect\Plugin\collect\Schema;

use Drupal\collect\Entity\Container;
use Drupal\collect\Plugin\collect\Schema\SchemaInterface;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\inmail\MIME\EntityInterface;
use Drupal\inmail\MIME\MultipartEntity;
use Drupal\inmail\MIME\ParseException;
use Drupal\inmail\MIME\Parser;
use Drupal\inmail\MIME\Renderer;
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
  public function render(Container $container) {
    $entity = NULL;
    try {
      $entity = $this->parseContainer($container);
    }
    catch (ParseException $exception) {
      return array('#markup' => $this->t('Message could not be parsed.'));
    }

    return $this->renderer->renderEntity($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function extractText(Container $container) {
    try {
      $entity = $this->parseContainer($container);
    }
    catch (ParseException $exception) {
      return NULL;
    }

    return $this->renderEntityPlainText($entity);
  }

  /**
   * Extracts the MIME Entity from an Inmail-defined Container.
   *
   * @param \Drupal\collect\Entity\Container $container
   *   A Collect Container entity.
   *
   * @return \Drupal\inmail\MIME\EntityInterface
   *   The parsed MIME entity.
   *
   * @throws \InvalidArgumentException
   *   If the schema does not match.
   * @throws \Drupal\inmail\MIME\ParseException
   *   If parsing failed.
   */
  protected function parseContainer(Container $container) {
    if ($container->getSchemaUri() != $this->getPluginId()) {
      throw new \InvalidArgumentException('Container schema does not match plugin.');
    }
    $data = json_decode($container->getData());
    $raw = $data->raw;
    return $this->parser->parse($raw);
  }

  protected function renderEntityPlainText(EntityInterface $entity) {
    if (!$entity instanceof MultipartEntity) {
      // Add body if it is safe.
      $body = $entity->getDecodedBody();
      if (empty($body)) {
        return NULL;
      }
      // @todo This hack prevents "one<br>two" from becoming "onetwo".
      $body = str_replace('>', '> ', $body);
      // Strip HTML.
      return Xss::filter($body, []);
    }
    else {
      // Add each contained body part.
      // @todo Avoid including text of message(s) that this is a reply to.
      $output = "\n\n";
      foreach ($entity->getParts() as $part) {
        $output .= $this->renderEntityPlainText($part) . "\n\n";
      }
      // Trim the trailing double newline.
      return trim($output);
    }
  }

}
