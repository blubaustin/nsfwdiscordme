#!/usr/bin/env bash
set -o nounset -o pipefail -o errexit

set -o allexport
source "$(dirname "$0")/../.env-prod"
set +o allexport

ansible-playbook -i hosts -e ansible_python_interpreter=/usr/bin/python3 ansible/site.yml
