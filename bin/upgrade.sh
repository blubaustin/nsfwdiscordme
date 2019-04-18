#!/usr/bin/env bash
git pull && \
bin/version.sh && \
yarn run build
