This is a super basic laravel install for doing common tests on the fortrabbit platform.


## Local development
To start up a local apache host on port 80:
```
docker-compose up --build
```


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


