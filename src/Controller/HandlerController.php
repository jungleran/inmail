<?php
/**
 * @file
 * Contains \Drupal\inmail\Controller\HandlerController.
 */

namespace Drupal\inmail\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\inmail\Entity\Handler;
use Drupal\inmail\HandlerManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Route controller for message handlers.
 */
class HandlerController extends ControllerBase {

  /**
   * @var \Drupal\inmail\HandlerManager
   */
  protected $handlerManager;

  /**
   * Constructs a HandlerController.
   */
  public function __construct(HandlerManager $handler_manager) {
    $this->handlerManager = $handler_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.inmail.handler'));
  }

  /**
   * Returns a title for the handler configuration edit page.
   */
  public function titleEdit(Handler $inmail_handler) {
    return $this->t('Configure %label handler', array('%label' => $inmail_handler->label()));
  }

  /**
   * Enables a message handler.
   */
  public function enable(Handler $inmail_handler) {
    $inmail_handler->enable()->save();
    return new RedirectResponse(\Drupal::url('inmail.handler_list'));
  }

  /**
   * Disables a message handler.
   */
  public function disable(Handler $inmail_handler) {
    $inmail_handler->disable()->save();
    return new RedirectResponse(\Drupal::url('inmail.handler_list'));
  }

}
