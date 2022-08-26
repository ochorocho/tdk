#!/usr/bin/env bash

sudo a2dismod php8.0
sudo a2dismod mpm_event
sudo a2enmod php8.0
sudo service apache2 restart