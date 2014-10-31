<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\Form\BounceProcessingSettingsForm.
 */

namespace Drupal\bounce_processing\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for general Bounce Processing configuration.
 */
class BounceProcessingSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bounce_processing_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('bounce_processing.settings');

    $form['return_path'] = array(
      '#title' => $this->t('Return-Path address'),
      '#type' => 'textfield',
      '#description' => $this->t('Normally the site email address (%site_mail) is used for the <code>Return-Path</code> header in outgoing messages. You can use this field to set another, dedicated address, or leave it empty to use the site email address.',
          ['%site_mail' => \Drupal::config('system.site')->get('mail')]),
      '#element_validate' => ['::validateReturnPath'],
      '#default_value' => $config->get('return_path'),
    );

    $form['verp'] = array(
      '#title' => $this->t('Enable VERP'),
      '#type' => 'checkbox',
      '#description' => $this->t('Choose whether to use <dfn>Variable Envelope Return Path</dfn> (VERP) to reliably identify the intended recipient of bounce messages.'),
      '#default_value' => $config->get('verp'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('bounce_processing.settings')
      ->set('return_path', $form_state->getValue('return_path'))
      ->set('verp', $form_state->getValue('verp'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Validates the Return-Path value.
   */
  public function validateReturnPath(array &$element, FormStateInterface $form_state, array &$complete_form) {
    $address = $element['#value'];

    // Check email format.
    if (!valid_email_address($address)) {
      $form_state->setError($element, $this->t('This is not a valid email address.'));
    }

    // Make sure the given address works with the VERP parse rules.
    if (preg_match('/\+.*@/', $address)) {
      $form_state->setError($element, $this->t('The address may not contain a <code>+</code> character.'));
    }
  }
}
