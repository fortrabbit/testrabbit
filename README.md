# Test Rabbit

This is a repo that when deployed as a fortrabbit App uses all common features of the platform. 
It has an automatic test suite that is run whenever you visit the main page of the app.

This test app is designed to work on all supported PHP versions.


## Local environment

```bash
just start
```


## Deployment

This repo is already deployed to Apps in all regions. 
Whenever you push to the `master` branch on GitHub, 
a GitHub Action is triggered that deploys that commit to the live apps.

Further documentation for current platform: 
https://www.notion.so/fortrabbit/testrabbit-237ed2d09f6280a49ca8dfdced0310a4


### Initial setup for a new App

The app expects an environment variable: `APP_TYPE`. Possible values are `uni` or `pro`.


