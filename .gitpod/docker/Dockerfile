# You can find the new timestamped tags here: https://hub.docker.com/r/gitpod/workspace-full/tags
FROM gitpod/workspace-mysql

COPY /envvars.txt /envvars.txt
#RUN sudo cat /envvars.txt > /etc/apache2/envvars && cat /etc/apache2/envvars

RUN sudo a2dismod php8.1 mpm_prefork mpm_event mpm_worker
RUN sudo apt-get install -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" libapache2-mod-ph*

# @todo Reset required?!
# export APACHE_SERVER_NAME=$(gp url 8001 | sed -e s/https:\\/\\/// | sed -e s/\\///)