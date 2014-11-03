<?php
/**
 * @file
 * Contains \Drupal\inmail\MessageProcessorInterface.
 */

namespace Drupal\inmail;

/**
 * Provides methods to process an incoming message.
 */
interface MessageProcessorInterface {

  /**
   * Analyzes an incoming message and executes callbacks as appropriate.
   *
   * In the iconical case, the message indicates a failed delivery of an earlier
   * outgoing message to a receiver, and a callback sets the receiver's send
   * state to mute.
   *
   * @param string $raw
   *   A raw mail message.
   */
  public function process($raw);

  /**
   * Analyzes and executes callbacks for multiple messages.
   *
   * @param string[] $messages
   *   A list of raw mail messages.
   *
   * @see MessageProcessorInterface::process()
   */
  public function processMultiple(array $messages);

}
