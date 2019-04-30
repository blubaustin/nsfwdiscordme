These commands should be executed from the project root directory.

Copy the Symfony configuration:

```
cp .env .env-prod
```

Edit the `.env-prod` configuration file.

Edit the `ansible/hosts` file and then run:

```
ansible/ansible-playbook.sh
```
