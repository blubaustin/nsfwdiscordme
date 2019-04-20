#!/usr/bin/env bash
scp .env-prod ubuntu@54.164.34.127:/var/www/nsfwdiscordme.headzoo.io/.env-prod
ssh ubuntu@54.164.34.127 'cd /var/www/nsfwdiscordme.headzoo.io && bin/upgrade.sh'
