<?php

namespace Drupal\inmail_test\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\inmail\MIME\Encodings;
use Drupal\inmail\MIME\MultipartMessage;
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
   * Provides a view support for the given attachment (MIME entity) path.
   *
   * @param \Drupal\past\PastEventInterface $past_event
   *   The past event.
   * @param string $path
   *   The path to find a corresponding MIME entity. In case "*" is passed,
   *   the raw mail message content will be returned.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The response.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   *   Throws an exception in case of invalid event.
   */
  public function getAttachment(PastEventInterface $past_event, $path) {
    /** @var \Drupal\Inmail\Mime\MultipartMessage $message */
    $message = $this->getMessage($past_event);

    // @todo: Inject the service.
    /** @var \Drupal\inmail\MIME\MessageDecomposition $message_decomposition */
    $message_decomposition = \Drupal::service('inmail.message_decomposition');

    // Offer download of the raw email message.
    if ($path == '~') {
      $headers = [
        'Content-Disposition' => 'attachment; filename=original_message.eml',
        'Content-Type' => 'message/rfc822',
      ];
      return new Response($message->toString(), Response::HTTP_OK, $headers);
    }

    // Filter-out non-multipart messages.
    if (!$message instanceof MultipartMessage) {
      return new Response(NULL, Response::HTTP_NOT_FOUND);
    }

    // @todo: Do not allow direct access to mail parts.
    // Get the entity from the given path.
    if (!$entity = $message_decomposition->getEntityByPath($message, $path)) {
      return new Response(NULL, Response::HTTP_NOT_FOUND);
    }

    // Decode the attachment body.
    $decoded_body = Encodings::decode($entity->getBody(), $entity->getContentTransferEncoding());

    // Display images in the browser.
    $header = $entity->getHeader();
    if ($entity->getContentType()['type'] == 'image') {
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
