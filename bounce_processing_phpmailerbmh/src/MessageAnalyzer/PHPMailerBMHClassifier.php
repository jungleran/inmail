<?php
/**
 * @file
 * Contains \Drupal\bounce_processing_phpmailerbmh\MessageAnalyzer\PHPMailerBMHClassifier.
 */

namespace Drupal\bounce_processing_phpmailerbmh\MessageAnalyzer;

use Drupal\bounce_processing\DSNType;
use Drupal\bounce_processing\Message;
use Drupal\bounce_processing\MessageAnalyzer\BounceClassifier;

/**
 * Message Classifier wrapper for cfortune's BounceHandler class.
 */
class PHPMailerBMHClassifier extends BounceClassifier {

  protected $rulecatStatusMap = array(
    'unknown' => '5.1.1',
    'full' => '5.2.2',
    'unrecognized' => '2.0.0',
    // @todo Cover all rule_cats...
  );

  /**
   * {@inheritdoc}
   */
  public function classify(Message $message) {
    require_once $this->getLibraryPath() . '/lib/BounceMailHandler/phpmailer-bmh_rules.php';
    $result = bmhBodyRules($message->getBody(), NULL);
    if (isset($this->rulecatStatusMap[$result['rule_cat']])) {
      $status = $this->rulecatStatusMap[$result['rule_cat']];
      $type = DSNType::parse($status);
      $type->setRecipient($result['email']);
      return $type;
    }
    return NULL;
  }

  protected function getLibraryPath() {
    $composer_manager_vendor_path = \Drupal::config('composer_manager.settings')->get('vendor_dir');
    return DRUPAL_ROOT . '/' . $composer_manager_vendor_path . '/' . 'instaclick/bounce-mail-handler';
  }

}
