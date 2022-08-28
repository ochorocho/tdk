# You can find the new timestamped tags here: https://hub.docker.com/r/gitpod/workspace-full/tags
FROM gitpod/workspace-mysql

RUN sudo a2dismod php8.1 mpm_prefork mpm_event mpm_worker

# Install all available libapache2-mod-php versions
RUN sudo apt-get update && \
    sudo apt-get install -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" libapache2-mod-php* php*-xdebug && \
    sudo apt-get clean
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