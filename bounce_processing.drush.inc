<?php
/**
 * @file
 * Drush commands for the Bounce Processing module.
 */

/**
 * Implements hook_drush_commands().
 */
function bounce_processing_drush_command() {
  $items = array();

  $items['bounce-process'] = array(
    'description' => 'Process a mail message.',
    'arguments' => array(
      'message' => 'A raw mail message',
    ),
    'drupal dependencies' => array('bounce_processing'),
  );

  return $items;
}

/**
 * Drush command implementation for processing a mail message.
 *
 * @param string $raw
 *   A raw mail message.
 */
function drush_bounce_processing_bounce_process($raw) {
  \Drupal::service('bounce.processor')->process($raw);
}
