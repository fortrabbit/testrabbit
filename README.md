# Test Rabbit

This is a repo that when deployed as a fortrabbit App uses all common features of the platform. It has an automatic test suite that is run whenever you visit the main page of the app.

## Local environment

```bash
docker-compose up -d
```

or

```bash
sail up -d
```

## Deployment

This repo is already deployed to Apps in all regions and whenever you push to the `master` branch on github, a Github Action is triggered that deploys that commit to the live apps.

### Live Apps
- eu2
    - [Dashboard testrabbit-pro](https://dashboard.fortrabbit.com/apps/testrabbit-pro) - https://testrabbit-pro.frb.io/
    - [Dashboard testrabbit-uni](https://dashboard.fortrabbit.com/apps/testrabbit-uni) -https://testrabbit-uni.frb.io/
- us1
    - [Dashboard testrabbit-us1](https://dashboard.fortrabbit.com/apps/testrabbit-us1) - https://testrabbit-us1.frb.io/
    - [Dashboard testrabbit-us1-u](https://dashboard.fortrabbit.com/apps/testrabbit-us1-u) - https://testrabbit-us1-u.frb.io/
- usx
    - [Dashboard testrabbit-pro](https://dashboard-dev.frbit.com/apps/testrabbit-pro) - 
    - [Dashboard testrabbit-uni](https://dashboard-dev.frbit.com/apps/testrabbit-uni) - 

To set up in git:

    git remote add eu       testrabbit-uni@deploy.eu2.frbit.com:testrabbit-uni.git
    git remote add eu2-pro  testrabbit-pro@deploy.eu2.frbit.com:testrabbit-pro.git
    git remote add origin   git@github.com:fortrabbit/testrabbit.git
    git remote add us-pro   testrabbit-us1@deploy.us1.frbit.com:testrabbit-us1.git
    git remote add us-uni   testrabbit-us1-u@deploy.us1.frbit.com:testrabbit-us1-u.git
    git remote add usx      testrabbit-uni@deploy.usx.frbit.com:testrabbit-uni.git
    git remote add usx-pro  testrabbit-pro@deploy.usx.frbit.com:testrabbit-pro.git


### Initial setup for a new App

The app expects an environment variable: `APP_TYPE`. Possible values are `uni` or `pro`.


