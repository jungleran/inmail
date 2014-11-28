<?php
/**
 * @file
 * Contains \Drupal\inmail\Plugin\inmail\Analyzer\StandardDSNAnalyzer.
 */

namespace Drupal\inmail\Plugin\inmail\Analyzer;

use Drupal\inmail\DSNStatus;
use Drupal\inmail\Message;
use Drupal\inmail\MessageAnalyzer\Result\AnalyzerResultWritableInterface;

/**
 * Identifies standard Delivery Status Notification (DSN) messages.
 *
 * This analyzer parses headers and multipart message parts according to the
 * standards defined in
 * @link http://tools.ietf.org/html/rfc3464 RFC 3464 @endlink. It aims to
 * identify:
 *   - whether the message is a bounce message or not,
 *   - the bounce status code reported by the mail server, and
 *   - the indented recipient of the message that bounced.
 *
 * This analyzer will likely fail to identify non-standard messages. This
 * behaviour is intended for the sake of simplicity; other analyzers may be
 * enabled to accomplish more reliable bounce message classification.
 *
 * @ingroup analyzer
 *
 * @Analyzer(
 *   id = "dsn",
 *   label = @Translation("Standard DSN Analyzer")
 * )
 */
class StandardDSNAnalyzer extends AnalyzerBase {

  /**
   * {@inheritdoc}
   */
  public function analyze(Message $message, AnalyzerResultWritableInterface $result) {
    // DSN's are declared with the 'Content-Type' header. Example:
    // Content-Type: multipart/report; report-type=delivery-status;
    // boundary="boundary_2634_73ab76f8"
    if (strpos($message->getHeader('Content-Type'), 'report-type=delivery-status') === FALSE) {
      // Ignore the message if it does not look like a DSN.
      return;
    }

    // Get the third body part, which has an easy-to-parse, standardized format.
    $parts = $message->getParts();
    if (!isset($parts[2])) {
      // Malformed message, give up.
      return;
    }
    $machine_part = $parts[2];

    // Parse the 'Status:' pseudo-header.
    if (preg_match('/\nStatus\s*:\s*([245])\.(\d{1,3})\.(\d{1,3})/i', $machine_part, $matches)) {
      $result->setBounceStatusCode(new DSNStatus($matches[1], $matches[2], $matches[3]));
    }

    // Parse the 'Final-Recipient:' pseudo-header.
    if (preg_match('/\nFinal-Recipient\s*:[^;]*;\s*(\S*@\S*\.\S*)/i', $machine_part, $matches)) {
      $result->setBounceRecipient($matches[1]);
    }
  }

}
