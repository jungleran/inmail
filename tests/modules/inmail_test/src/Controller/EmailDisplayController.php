<?php

namespace Drupal\inmail_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\inmail\MIME\Encodings;
use Drupal\past\PastEventInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Test email display controller.
 */
class EmailDisplayController extends ControllerBase {

  /**
   * Renders the email argument display of an past event.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   Throws an exception in case of invalid event.
   */
  public function formatDisplay(PastEventInterface $past_event, $view_mode) {
    $message = $this->getMessage($past_event);

    $build['#title'] = t('Email display');
    $build['email'] = [
      '#type' => 'inmail_message',
      '#message' => $message,
      '#view_mode' => $view_mode,
      '#download_url' => Url::fromRoute('inmail_test.attachment_download', ['past_event' => $past_event->id()]),
    ];

    return $build;
  }

  /**
   * Provides download support for an attachment.
   *
   * @param \Drupal\past\PastEventInterface $past_event
   *   The past event.
   * @param string $index
   *   The index of the attachment message part.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   Throws an exception in case of invalid event.
   */
  public function getAttachment(PastEventInterface $past_event, $index) {
    /** @var \Drupal\Inmail\Mime\MultipartMessage $message */
    $message = $this->getMessage($past_event);

    // @todo: Extend support for inline elements after
    //    https://www.drupal.org/node/2819713.
    if ($index == 'raw') {
      $attachment = $message;
    }
    else {
      $attachment = $message->getPart($index);
    }
    $header = $attachment->getHeader();

    // Decode the attachment body.
    $decoded_body = Encodings::decode($attachment->getBody(), $attachment->getContentTransferEncoding());

    // Display images in the browser.
    if ($attachment->getContentType()['type'] == 'image') {
      $header->removeField('Content-Disposition');
    }

    return new Response($decoded_body, Response::HTTP_OK, $header->toArray());
  }

  /**
   * Validates the past event and returns the parsed message.
   *
   * @param \Drupal\past\PastEventInterface $past_event
   *   The past event.
   *
   * @return \Drupal\inmail\MIME\Message|\Drupal\inmail\MIME\MessageInterface
   *   Returns the parsed message.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   Throws an exception in case of invalid event.
   */
  protected function getMessage(PastEventInterface $past_event) {
    // Throw an exception if the event is not created by Inmail or if the raw
    // message is not logged.
    if ($past_event->getModule() != 'inmail' || (!$raw_email_argument = $past_event->getArgument('email'))) {
      throw new NotFoundHttpException();
    }

    // @todo: Inject the parser service.
    /** @var \Drupal\inmail\MIME\Parser $parser */
    $parser = \Drupal::service('inmail.mime_parser');

    return $parser->parseMessage($raw_email_argument->getData());
  }

}
