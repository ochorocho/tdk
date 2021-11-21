#!/bin/bash

composer --version || exit 1
ddev version | grep "DDEV version" || exit 1
