#!/usr/bin/bash

export PATH=$PATH:/usr/sbin/

PHP_PACKAGES=""
# Get all available PHP versions
PHP_VERSIONS_AVAILABLE=($(apt list php* 2>/dev/null | grep '^php[0-9]\+\.[0-9]\+/' | grep -v 'php[0-9]\+\.[0-9]\+-[a-z]\+' | awk -F/ '{print $1}'))

split_index="$(printf "%s\n" "${PHP_VERSIONS_AVAILABLE[@]}" | grep -n "php$PHP_VERSION_UP" | cut -d: -f1)"
split_index="$((split_index - 1))"

PHP_VERSIONS=("${PHP_VERSIONS_AVAILABLE[@]:$split_index}")

echo "#####################################"
echo "${PHP_VERSIONS[@]}";
echo "#####################################"

for version in "${PHP_VERSIONS[@]}"
do
  if apt list php* 2>/dev/null | awk -F/ '$1 == "'"$version"'" {print $1}'; then
       PHP_PACKAGES+="libapache2-mod-$version $version-curl $version-zip $version-gd $version-intl $version-mysql $version-mbstring $version-xdebug $version-common $version-dom $version-xml "
  fi
done

apt-get install --no-install-recommends -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" $PHP_PACKAGES
apt-get install --no-install-recommends -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" \
    gnupg ca-certificates lsb-release bash-completion cron imagemagick apache2 golang-go mysql-server ghostscript

phpenmod zip curl gd intl mysqli pdo_mysql mbstring pdo xdebug dom xml
a2enmod alias authz_core autoindex deflate expires filter headers setenvif rewrite

# Setup Docker CE
# sudo mkdir -p /etc/apt/keyrings
# curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg && \

# Configure all available PHP versions + xdebug
bash /config/php-setup.sh
# Enable autocompletion
echo "source /etc/profile.d/bash_completion.sh" >> /etc/bash.bashrc

# Install mailpit
bash < <(curl -sL https://raw.githubusercontent.com/axllent/mailpit/develop/install.sh)

# Install composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer

# Cleanup image
apt-get autoremove -y
apt-get clean
rm -Rf /var/lib/mysql/* /var/lib/apt/lists /usr/share/doc/ /config

# Create log file and set permissions, otherwise it will be reported as missing
touch /var/log/apache2/xdebug.log /var/log/cron.log
mkdir -p /var/run/mysqld /var/log/mysql
chown -R gitpod:gitpod /etc/apache2 /home/gitpod /var/log/apache2 /etc/mysql /var/run/mysqld /var/log/mysql /var/lib/mysql /var/lib/mysql-files /var/lib/mysql-keyring /var/lib/mysql-upgrade /var/log/cron.log
chmod a+x /etc/init.d/mysql /etc/init.d/mailpit /usr/bin/tdk

# Required to run cronjob as user "gitpod" to avoid permission issues
echo "* * * * * gitpod /usr/bin/tdk cron >> /var/log/cron.log" > /etc/cron.d/typo3
