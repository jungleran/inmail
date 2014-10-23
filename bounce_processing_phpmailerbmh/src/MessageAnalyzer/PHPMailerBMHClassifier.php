<?php
/**
 * @file
 * Contains \Drupal\bounce_processing_phpmailerbmh\MessageAnalyzer\PHPMailerBMHClassifier.
 */

namespace Drupal\bounce_processing_phpmailerbmh\MessageAnalyzer;

use Drupal\bounce_processing\DSNStatusResult;
use Drupal\bounce_processing\Message;
use Drupal\bounce_processing\MessageAnalyzer\BounceClassifier;

/**
 * Message Classifier wrapper for cfortune's BounceHandler class.
 */
class PHPMailerBMHClassifier extends BounceClassifier {

  protected $rulecatStatusMap = array(
    'unknown' => '5.1.1',
    // Mailbox is full.
    'full' => '5.2.2',
    'unrecognized' => '2.0.0',
    // @todo Cover all rule_cats...
    // @todo Comment business logic.
  );

  /**
   * {@inheritdoc}
   */
  public function classify(Message $message) {
    require_once $this->getLibraryPath() . '/lib/BounceMailHandler/phpmailer-bmh_rules.php';
    $result = bmhBodyRules($message->getBody(), NULL);
    if (isset($this->rulecatStatusMap[$result['rule_cat']])) {
      $code = $this->rulecatStatusMap[$result['rule_cat']];
      $status = DSNStatusResult::parse($code);
      $status->setRecipient($result['email']);
      return $status;
    }
    return NULL;
  }

  protected function getLibraryPath() {
    $composer_manager_vendor_path = \Drupal::config('composer_manager.settings')->get('vendor_dir');
    return DRUPAL_ROOT . '/' . $composer_manager_vendor_path . '/' . 'instaclick/bounce-mail-handler';
  }

}
