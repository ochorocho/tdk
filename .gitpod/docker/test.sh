#!/bin/bash

composer --version || exit 1
php --version | grep "^PHP" || exit 1
php8.0 --version | grep "^PHP" || exit 1
php8.1 --version | grep "^PHP" || exit 1
apt list --installed | grep 'libapache2-mod-php'