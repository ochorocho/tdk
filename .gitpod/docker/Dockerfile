# Image on docker hub: https://hub.docker.com/r/ochorocho/gitpod-tdk/tags
FROM gitpod/workspace-base:latest

USER root

# Add custom config/scripts to container
COPY config /config
COPY config/apache.conf /etc/apache2/apache2.conf
COPY config/envvars.txt /etc/apache2/envvars
COPY scripts/mailhog /etc/init.d/mailhog
COPY scripts/mysql /etc/init.d/mysql
COPY scripts/tdk /usr/bin/
COPY scripts/tdk-completion /etc/bash_completion.d/
COPY config/mysql-client.cnf /etc/mysql/mysql.conf.d/client.cnf
COPY config/mysql.cnf /etc/mysql/mysql.conf.d/mysql.cnf
# Allow ImageMagick 6 to read/write pdf files
COPY config/imagemagick-policy.xml /etc/ImageMagick-6/policy.xml

# Install required packages
RUN add-apt-repository --yes ppa:ondrej/php && \
    apt-get update && \
    apt-get install --no-install-recommends -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" \
    bash-completion cron imagemagick apache2 libapache2-mod-php* php*-xdebug php*-curl php*-zip php*-gd php*-intl php*-mysql php*-mbstring golang-go mysql-server ghostscript php-xdebug-all-dev && \
    phpenmod zip curl gd intl mysqli pdo_mysql mbstring pdo xdebug && \
    a2enmod alias authz_core autoindex deflate expires filter headers setenvif rewrite && \
    # Configure all available PHP versions + xdebug
    bash /config/php-setup.sh && \
    # Enable autocompletion
    echo "source /etc/profile.d/bash_completion.sh" >> /etc/bash.bashrc && \
    # Install mailhog
    go get github.com/mailhog/MailHog && \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer && \
    # Cleanup image
    apt-get remove -y --purge \
    python3.8 python python3 python3.8-minimal gcc-7 build-essential apt-utils make linux-headers gcc g++ subversion mercurial emacs-bin-common emacsen-common vim-common vim && \
    apt-get autoremove -y && \
    apt-get clean && \
    # Create log file and set permissions, otherwise it will be reported as missing
    touch /var/log/apache2/xdebug.log /var/log/cron.log && \
    rm -Rf /var/lib/mysql/* /var/lib/apt/lists /usr/share/doc/ /usr/share/man/ && \
    mkdir -p /var/run/mysqld /var/log/mysql && \
    chown -R gitpod:gitpod /etc/apache2 /home/gitpod /var/log/apache2 /etc/mysql /var/run/mysqld /var/log/mysql /var/lib/mysql /var/lib/mysql-files /var/lib/mysql-keyring /var/lib/mysql-upgrade /var/log/cron.log && \
    chmod a+x /etc/init.d/mysql /etc/init.d/mailhog /usr/bin/tdk && \
    echo "* * * * * gitpod /usr/bin/tdk cron >> /var/log/cron.log" > /etc/cron.d/typo3

# @todo Reset required?!
# export APACHE_SERVER_NAME=$(gp url 8001 | sed -e s/https:\\/\\/// | sed -e s/\\///)