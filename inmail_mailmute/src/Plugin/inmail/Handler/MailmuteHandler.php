<?php
/**
 * @file
 * Contains \Drupal\inmail_mailmute\Plugin\inmail\Handler\MailmuteHandler.
 */

namespace Drupal\inmail_mailmute\Plugin\inmail\Handler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\inmail\Message;
use Drupal\inmail\MessageAnalyzer\Result\AnalyzerResultReadableInterface;
use Drupal\inmail\Plugin\inmail\Handler\HandlerBase;
use Drupal\inmail_mailmute\Plugin\mailmute\SendState\CountingBounces;
use Drupal\inmail_mailmute\Plugin\mailmute\SendState\PersistentSend;
use Drupal\mailmute\SendStateManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Reacts to bounce messages by managing the send state of the bouncing address.
 *
 * @ingroup mailmute
 *
 * @Handler(
 *   id = "mailmute",
 *   label = @Translation("Mailmute"),
 *   description = @Translation("Reacts to bounce messages by managing the send state of the bouncing address.")
 * )
 */
class MailmuteHandler extends HandlerBase implements ContainerFactoryPluginInterface {

  /**
   * The Mailmute send state manager.
   *
   * @var \Drupal\mailmute\SendStateManagerInterface
   */
  protected $sendstateManager;

  /**
   * The Inmail logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $loggerChannel;

  /**
   * Constructs a new MailmuteHandler.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, SendStateManagerInterface $sendstate_manager, LoggerChannelInterface $logger_channel) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->sendstateManager = $sendstate_manager;
    $this->loggerChannel = $logger_channel;
    $this->setConfiguration($configuration);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('plugin.manager.sendstate'),
      $container->get('logger.factory')->get('inmail')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function help() {
    return array(
      '#type' => 'item',
      '#markup' => $this->t('<p>Soft bounces trigger a transition to the <em>Counting bounces</em> state. After a number of bounces, the state transitions to <em>Temporarily unreachable</em>.</p> <p>Hard bounces cause the send state to transition to <em>Invalid address</em>.</p>'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function invoke(Message $message, AnalyzerResultReadableInterface $result) {
    // Only handle bounces.
    if (empty($result) || !$status_code = $result->getBounceStatusCode()) {
      return;
    }
    if ($status_code->isSuccess()) {
      return;
    }

    $log_context = ['%code' => $status_code->getCode()];

    // Only handle bounces with an identifiable recipient.
    if (!$address = $result->getBounceRecipient()) {
      // @todo Log the message body or place it in a moderation queue.
      $this->loggerChannel->info('Bounce with status %code received but no recipient identified.', $log_context);
      return;
    }

    $log_context += ['%address' => $address];

    // Only handle bounces with an identifiable recipient that we care about.
    if (!$this->sendstateManager->isManaged($address)) {
      $this->loggerChannel->info('Bounce with status %code received but recipient %address is not managed here.', $log_context);
      return;
    }

    $state = $this->sendstateManager->getState($address);

    // Block transition if current state is "Persistent send".
    if ($state instanceof PersistentSend) {
      $this->loggerChannel->info('Send state not transitioned for %address because state was %old_state', $log_context + ['%old_state' => 'persistent_send']);
      return;
    }

    $state_configuration = array(
      'code' => $result->getBounceStatusCode(),
      'reason' => $result->getBounceReason(),
    );

    // In the case of a "hard bounce", set the send state to a muting state.
    if ($status_code->isPermanentFailure()) {
      $this->sendstateManager->transition($address, 'inmail_invalid_address', $state_configuration);
      $this->loggerChannel->info('Bounce with status %code triggered send state transition of %address to %new_state', $log_context + ['%new_state' => 'inmail_invalid_address']);
      return;
    }

    // Not success and not hard bounce, so status must indicate a "soft bounce".
    // If already counting bounces, add 1.
    if ($state instanceof CountingBounces) {
      $state->increment();

      // If the threshold is reached, start muting.
      if ($state->getThreshold() && $state->getCount() >= $state->getThreshold()) {
        $this->sendstateManager->transition($address, 'inmail_temporarily_unreachable', $state_configuration);
        $this->loggerChannel->info('Bounce with status %code triggered send state transition of %address to %new_state', $log_context + ['%new_state' => 'inmail_temporarily_unreachable']);
      }
      else {
        $this->sendstateManager->save($address);
        $this->loggerChannel->info('Bounce with status %code triggered soft bounce count increment for %address', $log_context);
      }
      return;
    }

    // If still sending, start counting bounces.
    if (!$state->isMute()) {
      $this->sendstateManager->transition($address, 'inmail_counting', array('count' => 1, 'threshold' => $this->configuration['soft_threshold']) + $state_configuration);
      $this->loggerChannel->info('Bounce with status %code triggered send state transition of %address to %new_state', $log_context + ['%new_state' => 'inmail_counting']);
      return;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'soft_threshold' => 5,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['soft_threshold'] = array(
      '#title' => 'Soft bounce tolerance',
      '#type' => 'number',
      '#default_value' => $this->configuration['soft_threshold'],
      '#description' => $this->t('This defines how many soft bounces may be received from an address before its state is transitioned to "Temporarily unreachable".'),
      '#description_display' => 'after',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['soft_threshold'] = $form_state->getValue('soft_threshold');
  }

}
