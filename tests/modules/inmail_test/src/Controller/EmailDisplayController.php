<?php

namespace Drupal\inmail_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\past\PastEventInterface;

/**
 * Test email display controller.
 */
class EmailDisplayController extends ControllerBase {

  /**
   * Renders the email argument display of an past event.
   */
  public function formatDisplay(PastEventInterface $past_event, $view_mode) {
    $build['#title'] = t('Email display');

    if ($past_event->getModule() != 'inmail' || (!$raw_email_argument = $past_event->getArgument('email'))) {
      $build['no_display'] = [
        '#type' => 'item',
        '#markup' => t('Given past event needs to be created by Inmail and needs to have an "email" argument.'),
      ];
      return $build;
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
