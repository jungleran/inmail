#!/bin/bash
#
# Drupal Inmail processing script
#
# You can setup your local MTA (e.g. Postfix) to pipe incoming email to this
# script. It will then run the Inmail processor (analyzers and handlers) for
# each message. Useful for testing the processor on various kinds of email
# content.
#
# You might need to add the location of the drush command to the PATH.
#
# As example usage, you can configure Postfix to redirect mail to your mail
# spool, and put the following in ~/.forward (including quotes):
# "| PATH=/usr/local/bin:$PATH /var/www/drupal/modules/inmail/postfix-filter.sh"

# Parse options
while getopts d option; do
  case $option in
    d) # Dump environment information for debugging
      id
      set
      drush status
      drush inmail-services
      DRUSHOPTS=-d
      ;;
  esac
done

# Enter Drush environment
cd `dirname $0`

# Email content (one message) is piped from stdin to the Drush command
drush $DRUSHOPTS inmail-process
