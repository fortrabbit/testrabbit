# TestRabbit

This is used internally by fortrabbit to test basic features of the platform. When deployed as a fortrabbit app there is an automatic test suite that runs whenever you visit the main page of the app.

## Local environment

Running this locally doesn't really make sense, at it is designed to run on the fortrabbit platform. But you can build containers locally and run a simple test to ensure that composer will successfully install on all PHP versions.

```bash
just start
just test
```

## Deployment

This repo is deployed to apps in all regions on the old platform. Further documentation:  
https://github.com/fortrabbit/knowledge-base/blob/main/Old%20platform/Overview/testrabbit.md

### Initial setup for a new App

The app expects an environment variable: `APP_TYPE`. Possible values are `uni` or `pro`. Test.
