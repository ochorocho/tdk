#!/bin/bash

# Configure all php versions
# * 40-php.ini
# * 20-xdebug.ini

ALL_VERSIONS=$(ls /etc/php)
for version in $ALL_VERSIONS
do
    cat xdebug.ini | sudo tee /etc/php/${version}/apache2/conf.d/20-xdebug.ini > /dev/null
    echo -e "memory_limit=256M\nmax_execution_time=240\nmax_input_vars=1500" | sudo tee -a /etc/php/${version}/cli/conf.d/40-php.ini | sudo tee -a  /etc/php/${version}/apache2/conf.d/40-php.ini
done
