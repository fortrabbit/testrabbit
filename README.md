# TestRabbit

This is used internally by fortrabbit to test basic features of the platform. When deployed as a fortrabbit app there is an automatic test suite that runs whenever you visit the main page of the app.

It is a **zero-dependency plain-PHP app** (no framework, no Composer packages), so the exact same code runs on every PHP version the platform serves — old platform down to **7.4** and the new-platform k8s images (**8.1–8.5**). See `docs/superpowers/specs/` for the design (IN-1649).

## Layout

- `public/index.php` — front controller; routes are declared there.
- `app/Framework/` — the tiny router / view / response / request helpers.
- `app/Tests/` — one class per platform feature test (MySQL, MongoDB, APCu, …).
- `app/Support/` — ImageMagick perf runner, PHP-error emitter, rendition maths.
- `templates/` — plain-PHP templates (no Blade).
- `bin/job-*.php` — standalone CLI worker jobs (sleep / random-error / eat-memory).
- `config.php` / `bootstrap.php` — config array and bootstrap (autoload + `.env`).

## Local environment

Running this locally doesn't really make sense, as it is designed to run on the fortrabbit platform. But you can build the containers and smoke-test that the app boots and serves on every PHP version:

```bash
just start   # build + run one container per PHP version (7.4 → 8.5)
just test    # assert the MySQL feature test passes on each
```

There is no build step — the containers just serve the source directly.

## Deployment

This repo is deployed to apps in all regions on the old platform. Further documentation:  
https://github.com/fortrabbit/knowledge-base/blob/main/Old%20platform/Overview/testrabbit.md

### Initial setup for a new App

The app expects an environment variable: `APP_TYPE`. Possible values are `uni` or `pro`.
