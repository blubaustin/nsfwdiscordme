Installing
==========
The site is a standard Symfony 4 application which uses webpack to build assets.

### Requirements
* PHP 7.1+
* MySQL 5.7+
* Nginx 1.10+
* [Elasticsearch 6+](https://tecadmin.net/setup-elasticsearch-on-ubuntu/)
* [Redis 3+](https://tecadmin.net/install-redis-ubuntu/)

### Clone and Build
```
cd /var/www
git clone git@github.com:blubaustin/nsfwdiscordme.git
mv nsfwdiscordme www.nsfwdiscordme.com
cd www.nsfwdiscordme.com
composer install
yarn install
yarn run build
```

Ensure the `www-data` user owns all the files.

```
sudo chown -R www-data:www-data /var/www/www.nsfwdiscordme.com
sudo chmod -R g+w /var/www/www.nsfwdiscordme.com
```

### Configuration
Edit the `.env` configuration file and then run the migrations.

### Database
Create the database and user from the MySQL command line:

```
CREATE USER 'nsfwdiscordme'@'localhost' IDENTIFIED BY 'xxx';
CREATE DATABASE nsfwdiscordme CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON nsfwdiscordme.* TO 'nsfwdiscordme'@'localhost';
FLUSH PRIVILEGES;
```

Then run the Doctrine migration command to create the database tables.
```
bin/console doctrine:migrations:migrate
```

### Cron
Install the cron jobs using the `crontab -e` command.

```
@hourly /usr/bin/php /var/www/www.nsfwdiscordme.com/bin/console app:logs:check
@hourly /usr/bin/php /var/www/www.nsfwdiscordme.com/bin/console app:server:online
@daily /usr/bin/php /var/www/www.nsfwdiscordme.com/bin/console app:bumps:reset
0 */6 * * * /usr/bin/php /var/www/www.nsfwdiscordme.com/bin/console app:server:upgrades
```

### Nginx
Configure the Nginx virtual host. Create a new file `/etc/nginx/sites-available/nsfwdiscordme.conf` with the following configuration.

```
server {
        listen 80;
        server_name www.nsfwdiscordme.com;
        root /var/www/www.nsfwdiscordme.com/public;

        set $app index.php;
        index $app;

        error_log /var/log/nginx/www.nsfwdiscordme.com-error.log;
        access_log /var/log/nginx/www.nsfwdiscordme.com-access.log;

        gzip on;
        gzip_min_length 1000;
        gzip_types application/x-javascript application/javascript text/css application/json application/xml text/yaml;

        location / {
                try_files $uri $uri/ /$app?$query_string;
        }
        
        location ~* \.(?:jpg|jpeg|gif|png|ico|cur|gz|svg|svgz|mp4|ogg|ogv|webm|htc)$ {
                expires 1M;
                access_log off;
                add_header Cache-Control "public";
        }

        location ~* \.(?:css|js)$ {
                expires 1M;
                access_log off;
                add_header Cache-Control "public";
        }

        location ~ \.php$ {
                fastcgi_split_path_info ^(.+\.php)(/.+)$;
                fastcgi_pass unix:/var/run/php/php7.1-fpm.sock;
                fastcgi_index $app;
                include fastcgi_params;

                fastcgi_param APP_ENV "dev";
                fastcgi_param APP_SECRET "xxxx";
                fastcgi_param DATABASE_URL "mysql://<username>:<password>@localhost:3306/<dbname>";
                fastcgi_param MAILER_URL "xxx";
                fastcgi_param REDIS_HOST "localhost";
                fastcgi_param REDIS_PORT "6379";
                fastcgi_param DISCORD_CLIENT_ID "xxx";
                fastcgi_param DISCORD_CLIENT_SECRET "xxx";
                fastcgi_param DISCORD_BOT_TOKEN "xxx";
                fastcgi_param DISCORD_OAUTH_REDIRECT_URL "http://www.nsfwdiscordme.com/discord/oauth2/redirect";
                fastcgi_param RECAPTCHA_SITE_KEY "xxx";
                fastcgi_param RECAPTCHA_SECRET_KEY "xxx";
                fastcgi_param SNOWFLAKE_MACHINE_ID "1";
                fastcgi_param YUNOGASAI_ID "1";
                fastcgi_param YUNOGASAI_SECRET "xxx";
        }
}

```

*Note: the same environment variables from `.env` must be added to the configuration.*

Create a symbolic link to the `sites-enabled` directory and restart Nginx.

```
sudo ln -s /etc/nginx/sites-available/nsfwdiscordme.conf /etc/nginx/sites-enabled/nsfwdiscordme.conf
sudo service nginx restart
```
