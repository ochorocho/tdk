#!/bin/bash

composer --version || exit 1

# See php and required modules
ALL_VERSIONS="7.2 7.3 7.4 8.0 8.1"

for version in $ALL_VERSIONS
do
    "php$version" -v | grep "^PHP" || (echo "PHP curl 7.4 missing" && exit 1)
    "php$version" -m | grep "curl" || (echo "PHP curl 7.4 missing" && exit 1)
    "php$version" -m | grep "zip" || (echo "PHP zip 7.4 missing" && exit 1)
done

which /home/gitpod/go/bin/MailHog || (echo "MailHog Missing" && exit 1)
convert --version | grep "^Version" || (echo "convert missing" && exit 1)
identify --version | grep "^Version" || (echo "identify issing" && exit 1)
docker --version || (echo "Docker missing" && exit 1)