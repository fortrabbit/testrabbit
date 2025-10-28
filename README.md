# Test Rabbit

This is used internally by fortrabbit to test basic features of the platform.
When deployed as a fortrabbit app there is an automatic test suite that runs whenever you visit the main page of the app.


## Local environment
Running this locally doesn't really make sense, at it is designed to run on the fortrabbit platform.

But you can build containers locally and run a simple test to ensure that composer will successfully install on all PHP versions.

```bash
just start
just test
```


## Deployment

This repo is already deployed to apps in all regions on the current platform. 

Further documentation for current platform: 
https://www.notion.so/fortrabbit/testrabbit-237ed2d09f6280a49ca8dfdced0310a4


### Initial setup for a new App

The app expects an environment variable: `APP_TYPE`. Possible values are `uni` or `pro`.


