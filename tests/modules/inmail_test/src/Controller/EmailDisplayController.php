<?php

namespace Drupal\inmail_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\past\PastEventInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Test email display controller.
 */
class EmailDisplayController extends ControllerBase {

  /**
   * Renders the email argument display of an past event.
   *
   * @throws NotFoundHttpException When past event is not created by inmail.
   */
  public function formatDisplay(PastEventInterface $past_event, $view_mode) {
    $build['#title'] = t('Email display');

    if ($past_event->getModule() != 'inmail' || (!$raw_email_argument = $past_event->getArgument('email'))) {
      throw new NotFoundHttpException();
    }

    /** @var \Drupal\inmail\MIME\Parser $parser */
    $parser = \Drupal::service('inmail.mime_parser');
    $message = $parser->parseMessage($raw_email_argument->getData());

    $build['email'] = [
      '#type' => 'inmail_message',
      '#message' => $message,
      '#view_mode' => $view_mode,
    ];

    return $build;
  }

}
