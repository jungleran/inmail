<?php
/**
 * @file
 * Contains \Drupal\inmail\MessageAnalyzer\VERPAnalyzer.
 */

namespace Drupal\inmail\MessageAnalyzer;

use Drupal\inmail\AnalyzerResultInterface;
use Drupal\inmail\Message;

/**
 * Extracts a recipient address from a VERP 'To' header of a bounce.
 *
 * @see inmail_mail_alter_VERP()
 * @see https://en.wikipedia.org/wiki/VERP
 * @todo Document VERP here instead.
 */
class VERPAnalyzer implements MessageAnalyzerInterface {

  /**
   * {@inheritdoc}
   */
  public function analyze(Message $message, AnalyzerResultInterface $result) {
    // Cancel if VERP is disabled.
    if (!\Drupal::config('inmail.settings')->get('verp')) {
      return;
    }

    // Split the site address to facilitate matching.
    $return_path = \Drupal::config('inmail.settings')->get('return_path') ?: \Drupal::config('system.site')->get('mail');
    $return_path = explode('@', $return_path);

    // Match the modified Return-Path (returnpath+alice=example.com@website.com)
    // and put the parts of the recipient address (alice, example.com) in
    // $matches.
    // @todo $return_path might break the regex? Consider alternative parsing.
    if (preg_match(':^' . $return_path[0] . '\+(.*)=(.*)@' . $return_path[1] . '$:', $message->getHeader('To'), $matches)) {
      // Report the recipient address (alice@example.com).
      $result->setBounceRecipient($matches[1] . '@' . $matches[2]);
    }
  }

}
