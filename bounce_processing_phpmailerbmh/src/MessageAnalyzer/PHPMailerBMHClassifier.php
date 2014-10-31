<?php
/**
 * @file
 * Contains \Drupal\bounce_processing_phpmailerbmh\MessageAnalyzer\PHPMailerBMHClassifier.
 */

namespace Drupal\bounce_processing_phpmailerbmh\MessageAnalyzer;

use Drupal\bounce_processing\AnalyzerResultInterface;
use Drupal\bounce_processing\DSNStatusResult;
use Drupal\bounce_processing\Message;
use Drupal\bounce_processing\MessageAnalyzer\BounceClassifier;

/**
 * Message Classifier wrapper for the PHPMailer-BMH class.
 */
class PHPMailerBMHClassifier extends BounceClassifier {

  /**
   * Maps results from the library to appropriate DSN status codes.
   *
   * @var array
   *   An associative array with rule_cat values as keys and DSN status codes
   *   (strings) as keys.
   */
  protected static $rulecatStatusMap = array(
    // Sender blocked.
    'antispam' => '5.7.1',
    // "AutoReply message from...".
    'autoreply' => '2.0.0',
    // Invalid header, invalid structure, etc.
    'content_reject' => '5.6.0',
    // Transaction failed etc.
    'command_reject' => '5.5.0',
    // input/output error, can not open new email file.
    'internal_error' => '4.3.0',
    // System busy.
    'defer' => '4.4.1',
    // E.g. connection timed out.
    'delayed' => '4.0.0',
    // "mail for mta.example.com loops back to myself"
    'dns_loop' => '5.0.0',
    'dns_unknown' => '5.0.0',
    // Mailbox is full.
    'full' => '4.2.2',
    // @todo Cover all rule_cats...
    // Unknown user.
    'unknown' => '5.1.1',
    // Deliberately excluding 'unrecognized'.
  );

  /**
   * {@inheritdoc}
   */
  public function classify(Message $message, AnalyzerResultInterface $result) {
    // The analysis part of the library is in the bmhDSNRules and bmhBodyRules
    // functions.
    require_once $this->getLibraryPath() . '/lib/BounceMailHandler/phpmailer-bmh_rules.php';
    if ($message->isDSN()) {
      // The bmhDSNRules function takes the two report parts (human-readable and
      // computer-readable) as arguments.
      $bmh_result = bmhDSNRules($message->getParts()[1], $message->getParts()[2]);
    }
    else {
      $bmh_result = bmhBodyRules($message->getBody(), NULL, TRUE);
    }
    // The analysis returns an associative array designed for the library to
    // handle. It contains the following keys, of which rule_cat is the most
    // specific and usable:
    //   - remove: indicates that the message should be removed.
    //   - bounce_type: groups rule_cat values.
    //   - rule_cat: a string identifier for the reason for the bounce.
    //   - rule_no: references a single match condition in the code.
    //   - email: the recipient causing the bounce, if identifiable.
    if (isset(static::$rulecatStatusMap[$bmh_result['rule_cat']])) {
      $code = static::$rulecatStatusMap[$bmh_result['rule_cat']];
      if ($code) {
        $result->setBounceStatusCode(DSNStatusResult::parse($code));
        $result->setBounceRecipient($bmh_result['email']);
      }
    }
  }

  /**
   * Returns the path to the library defined by composer_manager.
   *
   * @return string
   *   The path to the PHPMailer-BMH libarary.
   */
  protected function getLibraryPath() {
    $composer_manager_vendor_path = \Drupal::config('composer_manager.settings')->get('vendor_dir');
    return DRUPAL_ROOT . '/' . $composer_manager_vendor_path . '/' . 'instaclick/bounce-mail-handler';
  }

}
