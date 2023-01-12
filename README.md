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
    - [https://dashboard.fortrabbit.com/apps/testrabbit-pro](https://dashboard.fortrabbit.com/apps/testrabbit-pro)
    - [https://dashboard.fortrabbit.com/apps/testrabbit-uni](https://dashboard.fortrabbit.com/apps/testrabbit-uni)
- us1
    - [https://dashboard.fortrabbit.com/apps/testrabbit-us1](https://dashboard.fortrabbit.com/apps/testrabbit-us1)
    - [https://dashboard.fortrabbit.com/apps/testrabbit-us1-u](https://dashboard.fortrabbit.com/apps/testrabbit-us1-u)
- usx
    - [https://dashboard-dev.frbit.com/apps/testrabbit-pro](https://dashboard-dev.frbit.com/apps/testrabbit-pro)
    - [https://dashboard-dev.frbit.com/apps/testrabbit-uni](https://dashboard-dev.frbit.com/apps/testrabbit-uni)

### Initial setup for a new App

The app expects an environment variable: `APP_TYPE`. Possible values are `uni` or `pro`.

