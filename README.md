[![Vardot](https://circleci.com/gh/Vardot/vardoc/tree/5.0.x.svg?style=shield)](https://app.circleci.com/pipelines/github/Vardot/vardoc/173/workflows/f363c3ca-ea02-462d-a25e-928742fd423a) Vardoc 5.0.0-rc1
# Vardoc Project

[![](https://www.drupal.org/files/styles/grid-3/public/project-images/Vardoc%20-%20No%20Padding.png)](https://www.drupal.org/project/vardoc)

Project template for [Vardoc distribution](http://www.drupal.org/project/vardoc).

## Create a Vardoc project with [Composer](https://getcomposer.org/download/):


# Install with Composer


To install the most recent stable release of Vardoc 5.0.x run this command:
```
composer create-project Vardot/vardoc-project:5.0.0-beta1 PROJECT_DIR_NAME --no-dev --no-interaction
```

To install the dev version of Vardoc 5.0.x run this command:
```
composer create-project vardot/vardoc-project:5.0.x-dev PROJECT_DIR_NAME --stability dev --no-interaction
```

## [CHANGELOG for Vardoc](https://github.com/Vardot/vardoc/blob/5.0.x/CHANGELOG.md)

## [Varbase Developer Guide](https://docs.varbase.vardot.com)

## [Automated Functional Testing](https://github.com/Vardot/vardoc/blob/5.0.x/tests/README.md)

## [Vardoc Gherkin features](https://github.com/Vardot/vardoc/blob/5.0.x/tests/features/vardoc/README.md)

## [General instructions on how to update Vardoc](https://github.com/Vardot/vardoc/blob/5.0.x/UPDATE.md)

## Local development with Lando

1. Install Lando locally, steps for installing can be found [here](https://docs.lando.dev/basics/installation.html).
2. Run Lando start.

## Debugging using Lando

- xDebug is enabled on Lando by default for PHP debugging.
- The debugger is set to listen for the port 9003 but can be changed in .lando/.php.ini
