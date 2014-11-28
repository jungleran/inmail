<?php
/**
 * @file
 * Contains \Drupal\inmail\Plugin\inmail\Analyzer\VERPAnalyzer.
 */

namespace Drupal\inmail\Plugin\inmail\Analyzer;

use Drupal\inmail\Message;
use Drupal\inmail\MessageAnalyzer\Result\AnalyzerResultWritableInterface;

/**
 * Extracts a recipient address from a VERP 'To' header of a bounce.
 *
 * Variable Envelope Return Path (VERP) is a method to reliably identify the
 * target recipient when analyzing a bounce message.
 *
 * The Return-Path header for outgoing messages is set to an address that
 * includes the address of the target recipient:
 * @code
 * bounce-mailbox '+' target-mailbox '=' target-host '@' bounce-host
 * @endcode
 * In other words, the recipient's address is appended to the Return-Path
 * address mailbox part, with a preceding '+' and with its '@' character
 * replaced by '='.
 *
 * Appending with '+' is known as "subaddress extension" and is described in RFC
 * 5233. Commonly, messages to foo+anything@example.com are delivered directly
 * to foo@example.com. Note that support for subaddress extension is limited
 * among mail services.
 *
 * @see inmail_mail_alter_VERP()
 *
 * @ingroup analyzer
 *
 * @Analyzer(
 *   id = "verp",
 *   label = @Translation("VERP Analyzer")
 * )
 */
class VERPAnalyzer extends AnalyzerBase {

  /**
   * {@inheritdoc}
   */
  public function analyze(Message $message, AnalyzerResultWritableInterface $result) {
    // Split the site address to facilitate matching.
    $return_path = \Drupal::config('inmail.settings')->get('return_path') ?: \Drupal::config('system.site')->get('mail');
    $return_path_split = explode('@', $return_path);

    if (count($return_path_split) != 2) {
      \Drupal::logger('imail')->warning('VERP Analyzer found invalid Return-Path address "%return_path"', array('%return_path' => $return_path));
      return;
    }

    // Match the modified Return-Path (returnpath+alice=example.com@website.com)
    // and put the parts of the recipient address (alice, example.com) in
    // $matches.
    if (preg_match(':^' . $return_path_split[0] . '\+(.*)=(.*)@' . $return_path_split[1] . '$:', $message->getHeader('To'), $matches)) {
      // Report the recipient address (alice@example.com).
      $result->setBounceRecipient($matches[1] . '@' . $matches[2]);
    }
  }

}
