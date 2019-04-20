#!/usr/bin/env bash
git reset --hard HEAD && \
git pull && \
bin/version.sh && \
rm .env && \
mv .env-prod .env && \
yarn run build && \
bin/console cache:clear
