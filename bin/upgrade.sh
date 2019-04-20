#!/usr/bin/env bash
git reset --hard HEAD && \
git pull && \
bin/version.sh && \
rm .env && \
mv .env-local .env && \
yarn run build && \
bin/console cache:clear
