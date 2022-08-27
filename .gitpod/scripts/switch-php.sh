#!/usr/bin/env bash

ARG=$1

PHP=${ARG:=8.1}
# @todo add help
ALL_VERSIONS=$(ls /etc/php)

if [[ ${ALL_VERSIONS[*]} =~ $PHP ]]
then
    echo "Switch to version $PHP."
else
    echo "PHP version $PHP not available"
    exit 1
fi

# Set CLI version
sudo update-alternatives --set php $(which php${PHP})

# Disable modules that might have been enabled
for version in $ALL_VERSIONS
do
    echo "Disable modules for PHP $version ..."
    sudo a2dismod mpm_prefork
    sudo a2dismod mpm_event
    sudo a2dismod mpm_worker
    sudo a2dismod "php$version"
done

# sudo service apache2 restart

sudo apt install -y -qq libapache2-mod-php$PHP

# if [ "$PHP" = "8.1" ]; then
#     sudo a2enmod "php8.1"
# fi

# if [ "$PHP" = "8.0" ]; then
#     sudo a2enmod "php8.0"
# fi

# if [ "$PHP" = "7.4" ]; then
#     sudo a2enmod "php5.6"
# fi

# if [ "$PHP" = "7.3" ]; then
#     sudo a2enmod "php5.6"
# fi

# if [ "$PHP" = "7.2" ]; then
#     sudo a2enmod "php5.6"
# fi

# if [ "$PHP" = "7.1" ]; then
#     sudo a2enmod "php5.6"
# fi

# if [ "$PHP" = "7.0" ]; then
#     sudo a2enmod "php5.6"
# fi

# if [ "$PHP" = "5.6" ]; then
#     sudo a2enmod "php5.6"
# fi

sudo a2enmod "php$PHP"
sudo service apache2 restart
