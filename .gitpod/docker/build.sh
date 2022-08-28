#!/bin/bash
#--no-cache
DOCKER_BUILDKIT=1 docker build --progress=plain . -f Dockerfile -t ochorocho/gitpod-tdk:latest && docker run --rm -it -v `pwd`/test.sh:/tmp/test.sh --entrypoint "bash" ochorocho/gitpod-tdk:latest /tmp/test.sh
docker images | grep ochorocho/gitpod-tdk
# docker push ochorocho/gitpod-tdk:latest