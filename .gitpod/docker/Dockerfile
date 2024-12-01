# Image on docker hub: https://hub.docker.com/r/ochorocho/gitpod-tdk/tags
FROM gitpod/workspace-base:latest

LABEL org.opencontainers.image.source=https://github.com/ochorocho/tdk

USER root

ARG php_version_and_up
ENV PHP_VERSION_UP=${php_version_and_up}
# Add custom config/scripts to container
COPY config /config
COPY config/apache.conf /etc/apache2/apache2.conf
COPY config/envvars.txt /etc/apache2/envvars
COPY scripts/mailpit /etc/init.d/mailpit
COPY scripts/mysql /etc/init.d/mysql
COPY scripts/tdk /usr/bin/
COPY scripts/tdk-completion /etc/bash_completion.d/
COPY config/mysql-client.cnf /etc/mysql/mysql.conf.d/client.cnf
COPY config/mysql.cnf /etc/mysql/mysql.conf.d/mysql.cnf
# Allow ImageMagick 6 to read/write pdf files
COPY config/imagemagick-policy.xml /etc/ImageMagick-6/policy.xml

# Install required packages
RUN add-apt-repository --yes ppa:ondrej/php && \
    bash /config/install-php.sh


# @todo Reset required?!
# export APACHE_SERVER_NAME=$(gp url 8001 | sed -e s/https:\\/\\/// | sed -e s/\\///)
