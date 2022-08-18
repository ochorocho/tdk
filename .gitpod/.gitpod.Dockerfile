FROM gitpod/workspace-mysql
SHELL ["/bin/bash", "-c"]

#TDK_PHP_VERSION=8.0,
#TDK_BRANCH=main
#TDK_USERNAME=ochorocho
#TDK_PATCH_REF=refs%2Fchanges%2F12%2F75512%2F2,
#TDK_PATCH_ID=75512/https://github.com/ochorocho/tdk/

https://gitpod.io/#TDK_PHP_VERSION=8.0,TDK_BRANCH=main,TDK_PATCH_REF=refs%2Fchanges%2F12%2F75512%2F2,TDK_USERNAME=ochorocho,TDK_PATCH_ID=75512/https://github.com/ochorocho/tdk/

ENV PHP_VERSION="${TDK_PHP_VERSION:=8.1}"
RUN sudo update-alternatives --set php $(which php${PHP_VERSION})