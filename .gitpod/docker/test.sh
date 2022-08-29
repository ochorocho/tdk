#!/bin/bash

composer --version || exit 1

# See php versions
php --version | grep "^PHP" || exit 1
php7.4 --version | grep "^PHP" || exit 1
php8.0 --version | grep "^PHP" || exit 1
php8.1 --version | grep "^PHP" || exit 1

# See required modules
php7.4 -m | grep "curl" || exit 1
php7.4 -m | grep "zip" || exit 1
php8.0 -m | grep "curl" || exit 1
php8.0 -m | grep "zip" || exit 1
php8.1 -m | grep "curl" || exit 1
php8.1 -m | grep "zip" || exit 1
which MailHog || exit 1
convert --version | grep "^Version" || exit 1
identify --version | grep "^Version" || exit 1
