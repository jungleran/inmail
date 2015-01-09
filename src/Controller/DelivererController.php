<?php
/**
 * @file
 * Contains \Drupal\inmail\Controller\DelivererController.
 */

namespace Drupal\inmail\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\inmail\Entity\DelivererConfig;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Route controller for mail deliverers.
 *
 * @ingroup deliverer
 */
class DelivererController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.inmail.deliverer'));
  }

  /**
   * Returns a title for the deliverer configuration edit page.
   */
  public function titleEdit(DelivererConfig $inmail_deliverer) {
    return $this->t('Configure deliverer %label', array('%label' => $inmail_deliverer->label()));
  }

  /**
   * Enables a mail deliverer.
   */
  public function enable(DelivererConfig $inmail_deliverer) {
    $inmail_deliverer->enable()->save();
    return new RedirectResponse(\Drupal::url('inmail.deliverer_list', [], ['absolute' => TRUE]));
  }

  /**
   * Disables a mail deliverer.
   */
  public function disable(DelivererConfig $inmail_deliverer) {
    $inmail_deliverer->disable()->save();
    return new RedirectResponse(\Drupal::url('inmail.deliverer_list', [], ['absolute' => TRUE]));
  }

}
