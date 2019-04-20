nsfwdiscordme
=============

# Installing
```
git clone git@github.com:blubaustin/nsfwdiscordme.git
cd nsfwdiscordme
composer install
yarn install
yarn run build
cp .env .env-local
```

Edit the `.env-local` configuration file and then run the migrations.

```
bin/console doctrine:migrations:migrate
```

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
                fastcgi_param SNOWFLAKE_MACHINE_ID "1";
        }
}

```

*Note: the same environment variables from `.env-local` must be added to the configuration.*

Add the conf file to the enabled sites and restart Nginx.

```
sudo ln -s /etc/nginx/sites-available/nsfwdiscordme.conf /etc/nginx/sites-enabled/nsfwdiscordme.conf
sudo service nginx restart
```
