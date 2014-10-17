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
    'description' => 'Process a mail message. A raw message must be provided on STDIN by piping.',
    'arguments' => array(
      'file...' => 'Zero or more file names to parse as messages.',
    ),
    'examples' => array(
      'drush bounce-process < 1.eml' => 'Process the message contained by the file 1.eml',
      'drush bounce-process 1.eml 2.eml 3.eml' => 'Process each of the messages contained by the three files',
    ),
    'drupal dependencies' => array('bounce_processing'),
  );

  return $items;
}

/**
 * Drush command implementation for processing a mail message.
 */
function drush_bounce_processing_bounce_process() {
  \Drupal::service('composer_manager.manager')->registerAutolaoder();
  if ($raw = _bounce_processing_get_piped_input()) {
    \Drupal::service('bounce.processor')->process($raw);
  }
  else {
    $raws = array();
    foreach (func_get_args() as $filename) {
      $raws[] = file_get_contents(__DIR__ . '/' . $filename);
    }
    \Drupal::service('bounce.processor')->processMultiple($raws);
  }
}


/**
 * Check for piped input.
 *
 * Stolen from http://deglos.com/blog/2011/09/19/unix-pipes-in-php
 *
 * @return string
 *   Data piped to Drush.
 */
function _bounce_processing_get_piped_input() {
  static $data = NULL;
  if (is_null($data)) {
    $data = '';
    stream_set_blocking(STDIN, FALSE);
    while ($input = fread(STDIN, 1024)) {
      $data .= $input;
    }
  }
  return $data;
}
