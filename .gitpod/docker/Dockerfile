# You can find the new timestamped tags here: https://hub.docker.com/r/gitpod/workspace-full/tags
FROM gitpod/workspace-mysql

COPY config/envvars.txt /envvars.txt
RUN sudo cat /envvars.txt > /etc/apache2/envvars

COPY config/mailhog /etc/init.d/mailhog
RUN sudo chmod a+x /etc/init.d/mailhog

RUN sudo a2dismod php8.1 mpm_prefork mpm_event mpm_worker
# Install all available libapache2-mod-php versions
RUN sudo apt-get install -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" libapache2-mod-ph*
RUN sudo apt-get -y install php*-xdebug
RUN go install github.com/mailhog/MailHog@latest

# @todo Reset required?!
# export APACHE_SERVER_NAME=$(gp url 8001 | sed -e s/https:\\/\\/// | sed -e s/\\///)