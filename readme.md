This is a super basic laravel install for doing common tests on the fortrabbit platform.


## Local development

before composer install

    touch  /T/fortrabbit/testrabbit/database/database.sqlite

    echo DB_CONNECTION=sqlite >> .env
    echo DB_DATABASE=/T/fortrabbit/testrabbit/database/database.sqlite >> .env

Set up the environment file

    echo APP_KEY=`php artisan key:generate --show` >> .env
    #echo LOG_CHANNEL=stack >> .env

Allow writing to storage/

    chown -R jaroslav:33 storage
    find storage/ -type f | xargs chmod 664 
    find storage/ -type d | xargs chmod 775 

Listen on localhost:80

    docker-compose up

### Inside the container

Enable error logging

    cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
    grep '^error_log' /usr/local/etc/php/php.ini

...keep php-errorlog as syslog or write to a file

    #sed -e 's@;error_log.*@error_log = /srv/app/usx-testing/storage/logs/laravel.log@' -i /usr/local/etc/php/php.ini
    #ln -s /srv/app/usx-testing/storage/logs/laravel.log /tmp/php_error.log

...make it appear in output of docker-compose up 

    sed -e "s/'default'.*/'default' => 'stderr',/" -i config/logging.php



## Deploying to fortrabbit
Select a laravel server, or otherwise follow this setup guide.

1. Generate an APP_KEY using this command
`php artisan key:generate --show`

2. Set the Root path to `/public`

3. Set these ENV vars on fortrabbit
```
APP_DEBUG=false
APP_ENV=production
APP_KEY= the key you generated
```



-------------
Unparenthesized `a ? b : c ? d : e` is deprecated. Use either 

    `(a ? b : c) ? d : e` or
    `a ? b : (c ? d : e)` 
    at /srv/app/usx-testing/vendor/twig/twig/lib/Twig/Node.php:42)

