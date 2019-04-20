#!/usr/bin/env bash
git reset --hard HEAD && \
git pull && \
bin/version.sh && \
yarn run build && \
bin/console cache:clear
