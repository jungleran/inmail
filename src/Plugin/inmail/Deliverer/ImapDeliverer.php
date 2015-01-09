<?php
/**
 * @file
 * Contains \Drupal\inmail\Deliverer\ImapDeliverer.
 */

namespace Drupal\inmail\Plugin\inmail\Deliverer;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Fetches messages over IMAP.
 *
 * @ingroup deliverer
 *
 * @Deliverer(
 *   id = "imap",
 *   label = @Translation("IMAP")
 * )
 */
class ImapDeliverer extends DelivererBase implements ContainerFactoryPluginInterface {

  /**
   * Injected Inmail logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $loggerChannel;

  /**
   * Injected site state.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerChannelInterface $logger_channel, StateInterface $state) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->loggerChannel = $logger_channel;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')->get('inmail'),
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function deliver() {
    // Get details from config and connect.
    // @todo Return noisily if misconfigured or imap missing. Possibly stop retrying.
    $mailbox_flags = $this->configuration['ssl'] ? '/ssl' : '';
    $mailbox = '{' . $this->configuration['host'] . ':' . $this->configuration['port'] . $mailbox_flags . '}';
    $imap_res = imap_open($mailbox, $this->configuration['username'], $this->configuration['password']);

    if (!$imap_res) {
      // @todo Consider throwing an exception.
      $this->loggerChannel->error('Deliverer connection failed: @error', ['@error' => implode("\n", imap_errors())]);
      return array();
    }

    // Find IDs of unread messages.
    // @todo Introduce options for message selection:
    //   - only read UNSEEN and mark unread
    //   - read all and delete (and optionally expunge)
    //   - keep track of current UID, read all with higher UID
    //   In the UI, warn about possible interference with other IMAP connections
    //   marking messages as read.
    $unread_ids = imap_search($imap_res, 'UNSEEN') ?: array();
    $batch_ids = array_splice($unread_ids, 0, $this->configuration['batch_size']);

    // Get the header + body of each message.
    $raws = array();
    foreach ($batch_ids as $unread_id) {
      $raws[] = imap_fetchheader($imap_res, $unread_id) . imap_body($imap_res, $unread_id);
    }

    // Save number of unread messages.
    // @todo Create a Monitoring sensor for this state key.
    $this->state->set('inmail.deliverer.imap.remaining', count($unread_ids));

    // Close resource and return messages.
    imap_close($imap_res);
    return $raws;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'host' => '',
      // Standard non-SSL IMAP port as defined by RFC 3501.
      'port' => 143,
      'ssl' => FALSE,
      'username' => '',
      'password' => '',
      'batch_size' => '100',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['info'] = array(
      '#type' => 'item',
      '#markup' => $this->t('Please refer to your email provider for the appropriate values for these fields.'),
    );

    $form['host'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Host'),
      '#default_value' => $this->configuration['host'],
    );

    $form['port'] = array(
      '#type' => 'number',
      '#title' => $this->t('Port'),
      '#default_value' => $this->configuration['port'],
      '#description' => $this->t('The standard port number is 143, or 993 when using SSL.'),
    );

    $form['ssl'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Use SSL'),
      '#default_value' => $this->configuration['ssl'],
    );

    $form['username'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#default_value' => $this->configuration['username'],
    );

    // Password field cannot have #default_value. To avoid forcing user to
    // re-enter password with each save, password updating is conditional on
    // this checkbox.
    $form['password_update'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Update password'),
    );

    $form['password'] = array(
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#states' => array(
        'visible' => array(
          ':input[name=password_update]' => array('checked' => TRUE),
        ),
      ),
    );

    // Always show password field if configuration is new.
    if ($form_state->getFormObject()->getEntity()->isNew()) {
      $form['password_update']['#access'] = FALSE;
      $form['password']['#states']['visible'] = array();
    }

    $form['batch_size'] = array(
      '#type' => 'number',
      '#title' => $this->t('Batch size'),
      '#default_value' => $this->configuration['batch_size'],
      '#description' => $this->t('How many messages to fetch on each invocation.'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $configuration = array(
      'host' => $form_state->getValue('host'),
      'port' => $form_state->getValue('port'),
      'ssl' => $form_state->getValue('ssl'),
      'username' => $form_state->getValue('username'),
      'batch_size' => $form_state->getValue('batch_size'),
    ) + $this->getConfiguration();

    // Only update password if "Update password" is checked.
    if ($form_state->getValue('password_update')) {
      $configuration['password'] = $form_state->getValue('password');
    }

    $this->setConfiguration($configuration);
  }

}
