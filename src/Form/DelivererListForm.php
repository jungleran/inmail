<?php

namespace Drupal\inmail\Form;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\inmail\Entity\DelivererConfig;
use Drupal\inmail\Plugin\inmail\Deliverer\FetcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Logger\RfcLogLevel;

/**
 * Wraps the deliverer list builder in a form, to enable interactive elements.
 *
 * @ingroup deliverer
 */
class DelivererListForm extends FormBase {

  /**
   * The injected deliverer plugin manager.
   *
   * @var \Drupal\Component\Plugin\PluginManagerInterface
   */
  protected $delivererManager;

  /**
   * Constructs a new DelivererListForm.
   */
  public function __construct(PluginManagerInterface $deliverer_manager) {
    $this->delivererManager = $deliverer_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('plugin.manager.inmail.deliverer'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'inmail_deliverer_list';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];

    // Add update button.
    $form['check'] = [
      '#type' => 'details',
      '#title' => $this->t('Operations'),
      '#open' => TRUE,
    ];
    $form['check']['check_button'] = [
      '#type' => 'submit',
      '#value' => $this->t('Check fetcher status'),
    ];
    $form['check']['process_button'] = [
      '#type' => 'submit',
      '#value' => $this->t('Process fetchers'),
      '#submit' => [
        [$this, 'submitFetchProcessing'],
      ],
    ];

    // phpcs:ignore -- Let the list builder render the table.
    $form['table'] = \Drupal::entityTypeManager()->getListBuilder('inmail_deliverer')->render();
    // Attach css library to the form.
    $form['#attached']['library'][] = 'inmail/inmail.admin';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Update status of each fetcher.
    $fetchers_count = 0;
    foreach (DelivererConfig::loadMultiple() as $deliverer_config) {
      // Get plugin instance.
      $deliverer = $this->delivererManager->createInstance($deliverer_config->getPluginId(), $deliverer_config->getConfiguration());
      // Update plugin.
      if ($deliverer instanceof FetcherInterface && $deliverer->isAvailable()) {
        $deliverer->update();
        $fetchers_count++;
      }
    }

    // Set a message and redirect to overview.
    if ($fetchers_count > 0) {
      $this->messenger()->addStatus($this->t('Fetcher state info has been updated.'));
    }
    else {
      $this->messenger()->addStatus($this->t('There are no configured fetchers, nothing to update.'));
    }
  }

  /**
   * Trigger processing of an active fetcher.
   */
  public function submitFetchProcessing(array &$form, FormStateInterface $form_state) {
    // phpcs:ignore -- Find active deliverers.
    $deliverer_ids = \Drupal::entityQuery('inmail_deliverer')->condition('status', TRUE)->execute();
    /** @var \Drupal\inmail\Entity\DelivererConfig[] $deliverers */
    // phpcs:ignore
    $deliverers = \Drupal::entityTypeManager()->getStorage('inmail_deliverer')->loadMultiple($deliverer_ids);
    /** @var \Drupal\inmail\MessageProcessorInterface $processor */
    // phpcs:ignore
    $processor = \Drupal::service('inmail.processor');
    $fetchers = [];
    $processed_count = 0;

    foreach ($deliverers as $deliverer) {
      // List only active fetchers.
      if ($deliverer->getPluginInstance() instanceof FetcherInterface && $deliverer->isAvailable()) {
        $fetchers[$deliverer->id()] = $deliverer->label();
        $raws = $deliverer->getPluginInstance()->fetchUnprocessedMessages();
        $results = $processor->processMultiple($raws, $deliverer);
        // Loop over processor results and check for failure.
        foreach ($results as $key => $result) {
          if (!$result->isSuccess()) {
            $messages = inmail_get_log_message($result, RfcLogLevel::ERROR);
            $this->messenger()->addError($this->t('Message @key: @error', [
              '@key' => $key,
              '@error' => strip_tags(implode("\n", $messages)),
            ]));
          }
          else {
            $processed_count++;
          }
        }

        // No more messages to process for specific deliverer?
        if ($deliverer->getPluginInstance()->getUnprocessedCount() != 0) {
          // @todo This message could be repeating.
          $this->messenger()->addStatus($this->t('There are more messages to process.'));
        }
        // @todo Add Batch API. https://www.drupal.org/node/2804337.
      }
    }

    // Processing finished, show final message.
    if (empty($fetchers)) {
      $this->messenger()->addWarning($this->t('There are no active fetchers. Please enable or <a href=":url">add</a> one.', [
        ':url' => '/admin/config/system/inmail/deliverers/add',
      ]));
    }
    elseif ($processed_count) {
      $this->messenger()->addStatus($this->t('Successfully processed @count messages by @fetchers.', [
        '@count' => $processed_count,
        '@fetchers' => implode(', ', $fetchers),
      ]));
    }
    else {
      $this->messenger()->addStatus($this->t('No messages to process.'));
    }
  }

}
