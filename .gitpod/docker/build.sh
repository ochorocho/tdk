#!/usr/bin/env bash

PUSH=""
LOAD=""
IMAGE_NAME="ghcr.io/ochorocho/gitpod-tdk"
IMAGE_VERSION="latest"

help() {
    echo "Available options:"
    echo "  * l - Load the image (--load)"
    echo "  * p - Push the image (--push)"
    echo "  * v - PHP versions to install, the given version and up (e.g. 8.1 will install 8.1, 8.2, 8.3, 8.4)"
}

while getopts ":v:hpl" opt; do
  case $opt in
  h)
    help
    exit 1
    ;;
  p)
    PUSH="--push"
    ;;
  l)
    LOAD="--load"
    ;;
  v)
    PHP_VERSION_AND_UP="${OPTARG}"
    ;;
  *)
    echo "Invalid option: -$OPTARG"
    help
    exit 1
    ;;
  esac
done

docker build --progress plain --no-cache --pull . -f Dockerfile -t $IMAGE_NAME:$IMAGE_VERSION --build-arg php_version_and_up="${PHP_VERSION_AND_UP:=8.1}" $PUSH $LOAD
