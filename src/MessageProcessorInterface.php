<?php
/**
 * @file
 * Contains \Drupal\bounce_processing\MessageProcessorInterface.
 */

namespace Drupal\bounce_processing;

/**
 * Provides methods to process an incoming message.
 */
interface MessageProcessorInterface {

  /**
   * Classifies an incoming message and executes callbacks as appropriate.
   *
   * In the iconical case, the message indicates a failed delivery of an earlier
   * outgoing message to a receiver, and the callback sets the receiver's send
   * state to mute.
   *
   * @param string $raw
   *   A raw mail message.
   */
  public function process($raw);

  /**
   * Classifies and executes callbacks for multiple messages.
   *
   * @param string[] $messages
   *   A list of raw mail messages.
   *
   * @see MessageProcessorInterface::process()
   */
  public function processMultiple(array $messages);

}
