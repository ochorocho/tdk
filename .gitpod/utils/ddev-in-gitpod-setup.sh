#!/usr/bin/env bash
DDEV_DIR="${GITPOD_REPO_ROOT}/.ddev"
mkdir $DDEV_DIR

if [ -z "$PHP_VERSION" ]; then
  PHP_VERSION="8.0"
fi

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

# Misc housekeeping before start
ddev config global --instrumentation-opt-in=true --omit-containers=ddev-router
