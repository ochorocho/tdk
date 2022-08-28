# Image on docker hub: https://hub.docker.com/r/ochorocho/gitpod-tdk/tags
FROM gitpod/workspace-mysql

RUN sudo a2dismod php8.1 mpm_prefork mpm_event mpm_worker

# Install all available libapache2-mod-php versions and php modules for these versions
RUN sudo apt-get update && \
    sudo apt-get install -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" \
    libapache2-mod-php* php*-xdebug php*-curl php*-zip && \
    sudo apt-get clean && \
    sudo phpenmod zip curl
RUN go install github.com/mailhog/MailHog@latest

COPY config /config
RUN sudo cat /config/envvars.txt > /etc/apache2/envvars
RUN sudo bash /config/setup.sh

COPY scripts/mailhog /etc/init.d/mailhog
RUN sudo chmod a+x /etc/init.d/mailhog

COPY scripts/tdk-* /usr/bin/
RUN sudo chmod a+x /usr/bin/tdk-*

# @todo Reset required?!
# export APACHE_SERVER_NAME=$(gp url 8001 | sed -e s/https:\\/\\/// | sed -e s/\\///)