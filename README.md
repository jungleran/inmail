Inmail
======

Incoming mail processing module for [Drupal](http://drupal.org/) 8.

Purpose:

- Fetch and process incoming mail messages
- Identify bounce messages
- Mute further sending to invalid mail addresses

Terminology:

Most Drupal code and documentation seems to use, interchangably, the terms
_email_ and _mail_ to denote both mail addresses and mail messages. Within
this module the mail scope is ubiquitous, so instead _address_ and _message_
are used. _Mail_ is used to refer to the technology as a whole.

@todo:

- [Inmail logo](https://www.drupal.org/node/2380975)
- [Integrate with simplytest.me](https://www.drupal.org/node/2381017)
- [Howto setup with postfix](https://www.drupal.org/node/2381019)
- [Generate Message-Id per module and pass incoming replies](https://www.drupal.org/node/2379829)
- [Offer List-Unsubscribe header](https://www.drupal.org/node/2381175)
- [Make sure IDN mail addresses work](https://www.drupal.org/node/2384677)
