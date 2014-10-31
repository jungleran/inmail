<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageAnalyzer\VERPAnalyzer.
 */

namespace Drupal\bounce_processing\MessageAnalyzer;

use Drupal\bounce_processing\AnalyzerResultInterface;
use Drupal\bounce_processing\Message;

/**
 * Extracts a recipient address from a VERP 'To' header of a bounce.
 *
 * @see bounce_processing_mail_alter_VERP()
 * @see https://en.wikipedia.org/wiki/VERP
 * @todo Document VERP here instead.
 */
class VERPAnalyzer implements MessageAnalyzerInterface {

  /**
   * {@inheritdoc}
   */
  public function analyze(Message $message, AnalyzerResultInterface $result) {
    if (\Drupal::config('bounce_processing.settings')->get('verp')) {
      $return_path = explode('@', \Drupal::config('system.settings')->get('site.mail'));
      // Match the modified Return-Path and put the parts of the recipient
      // address in $matches.
      // @todo $return_path could probably break the regex here?
      if (preg_match(':^' . $return_path[0] . '\+(.*)=(.*)@' . $return_path[1] . '$:', $message->getHeader('To'), $matches)) {
        $result->setBounceRecipient($matches[1] . '@' . $matches[2]);
      }
    }
  }
}
