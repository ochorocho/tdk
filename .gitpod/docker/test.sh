#!/bin/bash

composer --version || exit 1
php --version | grep "^PHP" || exit 1
php8.0 --version | grep "^PHP" || exit 1
php8.1 --version | grep "^PHP" || exit 1
which MailHog || exit 1
