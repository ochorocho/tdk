#!/bin/bash

composer --version || exit 1

# @todo, test tdk commands if possible
# export SSH_PRIVATE_KEY="-----BEGIN OPENSSH PRIVATE KEY----- b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAAAMwAAAAtzc2gtZW QyNTUxOQAAACDEMsv3oItZjfGpC37kIksDqV7awvGEhshuQKR4/CjhywAAAJBrwgvIa8IL yAAAAAtzc2gtZWQyNTUxOQAAACDEMsv3oItZjfGpC37kIksDqV7awvGEhshuQKR4/Cjhyw AAAEB3Cdx6Qg76bDTsdi2pdcAyFyeYE9KXRJwvPEW0hjcxJMQyy/egi1mN8akLfuQiSwOp XtrC8YSGyG5ApHj8KOHLAAAABmdpdHBvZAECAwQFBgc= -----END OPENSSH PRIVATE KEY-----"
# tdk ssh-add || (echo "Failed adding private key" && exit 1)

# See php and required modules
ALL_VERSIONS="8.1 8.2 8.3 8.4"

for version in $ALL_VERSIONS
do
    "php$version" -v | grep "^PHP" || (echo "PHP curl 7.4 missing" && exit 1)
    "php$version" -m | grep "curl" || (echo "PHP curl 7.4 missing" && exit 1)
    "php$version" -m | grep "zip" || (echo "PHP zip 7.4 missing" && exit 1)
done

which /usr/local/bin/mailpit || (echo "MailPit Missing" && exit 1)
convert --version | grep "^Version" || (echo "convert missing" && exit 1)
identify --version | grep "^Version" || (echo "identify issing" && exit 1)

