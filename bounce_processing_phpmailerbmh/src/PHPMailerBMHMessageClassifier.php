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

  /**
   * {@inheritdoc}
   */
  public function classify(Message $message) {
    require_once $this->getLibraryPath() . '/lib/BounceMailHandler/phpmailer-bmh_rules.php';
    $result = bmhBodyRules($message->getBody(), NULL);
    return $result['remove'] ? static::TYPE_BOUNCE : static::TYPE_REGULAR;
  }

  protected function getLibraryPath() {
    $composer_manager_vendor_path = \Drupal::config('composer_manager.settings')->get('vendor_dir');
    return DRUPAL_ROOT . '/' . $composer_manager_vendor_path . '/' . 'instaclick/bounce-mail-handler';
  }

}
