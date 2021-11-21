#!/usr/bin/env bash

# Download ddev images
ddev version | awk '/(drud|phpmyadmin)/ {print $2;}' >/tmp/images.txt
while IFS= read -r item
do
  docker pull "$item"
done < <(cat /tmp/images.txt)

# Setup
DDEV_DIR="${GITPOD_REPO_ROOT}/.ddev"
mkdir $DDEV_DIR

if [ -z "$PHP_VERSION" ]; then
  PHP_VERSION="8.0"
fi

# ddev config for Gitpod only, will override values in .ddev/config.yml
cat <<CONFIGEND > "${DDEV_DIR}"/config.gitpod.yaml
#ddev-gitpod-generated
php_version: "$PHP_VERSION"

bind_all_interfaces: true
host_webserver_port: 8080
# Will ignore the direct-bind https port, which will land on 2222
host_https_port: 2222
# Allows local db clients to run
host_db_port: 3306
# Assign MailHog port
host_mailhog_port: "8025"
# Assign phpMyAdmin port
host_phpmyadmin_port: 8036
CONFIGEND

# Set ddev specific environment variables
cat <<COMPOSEEND > "${DDEV_DIR}"/docker-compose.typo3.yaml
version: '3.6'
services:
   web:
    environment:
      - TYPO3_CONTEXT=Development
COMPOSEEND

# Misc housekeeping before start
ddev config global --instrumentation-opt-in=true --omit-containers=ddev-router
