# Auto Test Rabbit

A version of https://github.com/fortrabbit/testrabbit with a few of the tests automated.

## Development

```bash
docker-compose up -d
```

or

```bash
sail up -d
```

## Deployment

The app expects an environment variable: `APP_TYPE`. Possible values are `uni` or `pro`.


