<?php
/**
 * @file
 * Drush commands for the Inmail module.
 */

use Drupal\inmail\Entity\AnalyzerConfig;
use Drupal\inmail\Entity\HandlerConfig;

/**
 * @addtogroup processing
 * @{
 * The primary purpose of Drush integration for Inmail is to pipe incoming mail
 * to the message processor. You may want to configure your MTA to redirect
 * messages to a script that runs `drush inmail-process`. You can use the
 * provided postfix-filter.sh bash script or create your own.
 *
 * Another use for the Drush commands is manual testing during development. You
 * can evaluate the effects of various types of messages by feeding the email
 * source files to `drush inmail-process`, just as if the message had just been
 * fetched and forwarded by an MTA.
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

  $items['inmail-plugins'] = array(
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
 * Output the class names of current analyzers and handlers.
 */
function drush_inmail_plugins() {
  inmail_drush_setup();

  /** @var \Drupal\inmail\Entity\AnalyzerConfig[] $analyzer_configs */
  $analyzer_configs = AnalyzerConfig::loadMultiple();
  foreach ($analyzer_configs as $analyzer_config) {
    // Creating plugin instances enables us to detect if a config references a
    // missing plugin.
    /** @var \Drupal\inmail\Plugin\inmail\Analyzer\AnalyzerInterface $analyzer */
    $analyzer = \Drupal::service('plugin.manager.inmail.analyzer')->createInstance($analyzer_config->getPluginId());
    echo "Analyzer: {$analyzer_config->label()} ({$analyzer->getPluginId()})\n";
  }

  /** @var \Drupal\inmail\Entity\HandlerConfig[] $handler_configs */
  $handler_configs = HandlerConfig::loadMultiple();
  foreach ($handler_configs as $handler_config) {
    /** @var \Drupal\inmail\Plugin\inmail\Handler\HandlerInterface $handler */
    $handler = \Drupal::service('plugin.manager.inmail.handler')->createInstance($handler_config->getPluginId());
    echo "Handler: {$handler_config->label()} ({$handler->getPluginId()})\n";
  }
}

/**
 * Perform some preparation for interaction with the processing mechanism.
 */
function inmail_drush_setup() {
  if (\Drupal::moduleHandler()->moduleExists('composer_manager')) {
    \Drupal::service('composer_manager.manager')->registerAutoloader();
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

/**
 * @}
 */
