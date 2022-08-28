# Image on docker hub: https://hub.docker.com/r/ochorocho/gitpod-tdk/tags
FROM gitpod/workspace-mysql

# Install ImageMagick7
RUN sudo apt remove -y imagemagick imagemagick-6-common
RUN sudo apt-get install -y rlwrap checkinstall libwebp-dev libopenjp2-7-dev librsvg2-dev libde265-dev && \
    wget https://imagemagick.org/archive/ImageMagick.tar.gz && \
    tar xzvf ImageMagick.tar.gz && cd ImageMagick-* && \
    ./configure --enable-shared --with-modules --with-gslib && \
    sudo ldconfig /user/local/lib && \
    make && make install

RUN sudo a2dismod php8.1 mpm_prefork mpm_event mpm_worker

# Install all available libapache2-mod-php versions and php modules for these versions
RUN sudo apt-get update && \
    sudo apt-get install -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" \
    libapache2-mod-php* php*-xdebug php*-curl php*-zip php*-gd php*-intl php*-mysql && \
    sudo apt-get clean && \
    sudo phpenmod zip curl gd intl mysqli
RUN go install github.com/mailhog/MailHog@latest

COPY config /config
RUN sudo cat /config/envvars.txt > /etc/apache2/envvars
RUN sudo bash /config/setup.sh

COPY scripts/mailhog /etc/init.d/mailhog
RUN sudo chmod a+x /etc/init.d/mailhog

COPY scripts/tdk-* /usr/bin/
RUN sudo chmod a+x /usr/bin/tdk-*

# Cleanup image
RUN sudo apt remove -y --purge gcc-7 apt-utils python2.7 python3 make linux-headers gcc g++ subversion mercurial && \
    sudo apt autoremove -y

# @todo Reset required?!
# export APACHE_SERVER_NAME=$(gp url 8001 | sed -e s/https:\\/\\/// | sed -e s/\\///)