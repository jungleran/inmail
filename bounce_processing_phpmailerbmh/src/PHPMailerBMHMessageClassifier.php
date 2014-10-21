<?php
/**
 * @file
 * Contains \Drupal\bounce_processing_phpmailerbmh\PHPMailerBMHMessageClassifier.
 */

namespace Drupal\bounce_processing_phpmailerbmh;

use Drupal\bounce_processing\Message;
use Drupal\bounce_processing\MessageClassifierInterface;

/**
 * Message Classifier wrapper for cfortune's BounceHandler class.
 */
class PHPMailerBMHMessageClassifier implements MessageClassifierInterface {

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
      return $this->rulecatStatusMap[$result['rule_cat']];
    }
    return '2.0.0';
  }

  protected function getLibraryPath() {
    $composer_manager_vendor_path = \Drupal::config('composer_manager.settings')->get('vendor_dir');
    return DRUPAL_ROOT . '/' . $composer_manager_vendor_path . '/' . 'instaclick/bounce-mail-handler';
  }

}
