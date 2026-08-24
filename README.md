# TestRabbit

This is used internally by fortrabbit to test basic features of the platform. When deployed as a fortrabbit app there is an automatic test suite that runs whenever you visit the main page of the app.

## Local environment

Running this locally doesn't really make sense, at it is designed to run on the fortrabbit platform. But you can build containers locally and run a simple test to ensure that composer will successfully install on all PHP versions.

```bash
just start
just test
```

The PHPUnit suite runs in CI against PHP 8.3, 8.4, and 8.5. To run it in a
built container:

```bash
docker compose run --rm php83 php artisan test
```

## Deployment

This repo is deployed to apps in all regions on the old platform. Further documentation:  
https://github.com/fortrabbit/knowledge-base/blob/main/Old%20platform/Overview/testrabbit.md

### Initial setup for a new App

The app expects an environment variable: `APP_TYPE`. Possible values are `uni` or `pro`.

### Log volume generator

Visit `/log-volume` to start, monitor, and stop a log-volume run. The UI stores
run state in MySQL and dispatches short chained jobs through TestRabbit's
existing database queue. A normal worker must be running:

```bash
php artisan queue:work --sleep=5
```

Run `php artisan migrate --force` after deploying the feature. This also adds
the standard `jobs` table required by TestRabbit's configured database queue if
the platform database does not already have it.
