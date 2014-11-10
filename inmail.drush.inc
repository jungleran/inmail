<?php
/**
 * @file
 * Drush commands for the Inmail module.
 */

/**
 * Implements hook_drush_commands().
 */
function inmail_drush_command() {
  $items = array();

  $items['inmail-process'] = array(
    'description' => 'Process a mail message provided through STDIN, or one or more mail messages by filename.',
    'arguments' => array(
      'file [file2 ...]' => 'One or more file names to parse as messages. If no filenames are given, a message is read from STDIN.',
    ),
    'examples' => array(
      'cat 1.eml | drush inmail-process' => 'Process a message through STDIN',
      'drush inmail-process 1.eml 2.eml 3.eml' => 'Process each of the messages contained by the three files',
    ),
    'drupal dependencies' => array('inmail'),
  );

  $items['inmail-services'] = array(
    'description' => 'Tell which service classes are being used for analysis and handling.',
    'drupal dependencies' => array('inmail'),
  );

  return $items;
}

/**
 * Drush command implementation for processing a mail message.
 */
function drush_inmail_process() {
  inmail_drush_setup();

  $args = func_get_args();

  if (count($args) > 0) {
    $raws = array();
    foreach ($args as $filename) {
      $raws[] = file_get_contents($filename[0] == '/' ? $filename : __DIR__ . '/' . $filename);
    }
    \Drupal::service('inmail.processor')->processMultiple($raws);
  }
  else {
    $raw = _inmail_get_piped_input();
    \Drupal::service('inmail.processor')->process($raw);
  }
}

/**
 * Output the class names of current analysis and handling services.
 */
function drush_inmail_services() {
  inmail_drush_setup();

  $processor = \Drupal::service('inmail.processor');

  if ($analyzers = $processor->getAnalyzers()) {
    foreach ($analyzers as $class) {
      echo "Analyzer: $class\n";
    }
  }
  else {
    echo "No analyzers registered.\n";
  }

  if ($handlers = $processor->getHandlers()) {
    foreach ($handlers as $class) {
      echo "Handler: $class\n";
    }
  }
  else {
    echo "No handlers registered.\n";
  }
}

/**
 * Perform some preparation for interaction with the processing mechanism.
 */
function inmail_drush_setup() {
  if (\Drupal::moduleHandler()->moduleExists('composer_manager')) {
    \Drupal::service('composer_manager.manager')->registerAutolaoder();
  }
}


/**
 * Check for piped input.
 *
 * @return string
 *   Data piped to Drush.
 */
function _inmail_get_piped_input() {
  static $data = NULL;
  if (is_null($data)) {
    $data = '';
    while ($input = fread(STDIN, 1024)) {
      $data .= $input;
    }
  }
  return $data;
}
