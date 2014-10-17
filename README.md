Bounce processing
=================

Mail bounce message handler module for [Drupal](http://drupal.org/) 8.

Purpose:

- Fetch and process incoming mail messages
- Identify bounce messages
- Mute further sending to invalid mail addresses

Terminology:

Most Drupal code and documentation seems to use, interchangably, the terms
_email_ and _mail_ to denote both mail addresses and mail messages. Within
this module the mail scope is ubiquitous, so instead _address_ and _message_
are used. _Mail_ is used to refer to the technology as a whole.
