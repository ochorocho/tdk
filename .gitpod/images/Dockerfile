FROM gitpod/workspace-full
SHELL ["/bin/bash", "-c"]

RUN sudo apt-get -qq update && sudo apt-get install apt-transport-https ca-certificates -y && sudo update-ca-certificates

# Install ddev and some handy tools
RUN brew install drud/ddev/ddev tig

# Install latest composer
RUN curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/bin --filename=composer
