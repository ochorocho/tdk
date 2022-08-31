# Image on docker hub: https://hub.docker.com/r/ochorocho/gitpod-tdk/tags
FROM gitpod/workspace-base

USER root

# Add custom config/scripts to container
COPY config /config

COPY scripts/mailhog /etc/init.d/mailhog
RUN chmod a+x /etc/init.d/mailhog

COPY scripts/tdk-* /usr/bin/
RUN chmod a+x /usr/bin/tdk-*
# Allow ImageMagick 6 to read/write pdf files
COPY config/imagemagick-policy.xml /etc/ImageMagick-6/policy.xml

# Install required packages
RUN add-apt-repository ppa:ondrej/php
RUN apt-get update && \
    apt-get install --no-install-recommends -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" \
    imagemagick apache2 libapache2-mod-php* php*-xdebug php*-curl php*-zip php*-gd php*-intl php*-mysql php*-mbstring golang-go mysql-server ghostscript php-xdebug-all-dev && \
    mkdir -p /var/run/mysqld /var/log/mysql && \
    chown -R gitpod:gitpod /etc/mysql /var/run/mysqld /var/log/mysql /var/lib/mysql /var/lib/mysql-files /var/lib/mysql-keyring /var/lib/mysql-upgrade && \
    phpenmod zip curl gd intl mysqli pdo_mysql mbstring pdo xdebug && \
    a2enmod alias authz_core autoindex deflate expires filter headers setenvif rewrite && \
    # Configure all available PHP versions + xdebug
    bash /config/php-setup.sh

# Install mailhog
RUN go get github.com/mailhog/MailHog
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Configure mysql
COPY config/mysql-client.cnf /etc/mysql/mysql.conf.d/client.cnf
COPY config/mysql.cnf /etc/mysql/mysql.conf.d/mysql.cnf
COPY scripts/mysql /etc/init.d/mysql

# Cleanup image
RUN apt-get remove -y --purge \
    gcc-7 build-essential apt-utils python2.7 python3 make linux-headers gcc g++ subversion mercurial && \
    apt-get autoremove -y && \
    apt-get clean

# Create log file and set permissions, otherwise it will be reported as missing
RUN chown -R gitpod:gitpod /etc/apache2
RUN touch /var/log/apache2/xdebug.log && \
    chown -R gitpod:gitpod /var/log/apache2

USER gitpod
COPY config/apache.conf /etc/apache2/apache2.conf
COPY config/envvars.txt /etc/apache2/envvars

# @todo Reset required?!
# export APACHE_SERVER_NAME=$(gp url 8001 | sed -e s/https:\\/\\/// | sed -e s/\\///)