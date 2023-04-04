# me-cms

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.txt)
[![Build Status](https://travis-ci.org/mirko-pagliai/me-cms.svg?branch=master)](https://travis-ci.org/mirko-pagliai/me-cms)
[![codecov](https://codecov.io/gh/mirko-pagliai/me-cms/branch/master/graph/badge.svg)](https://codecov.io/gh/mirko-pagliai/me-cms)
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/f42666d11ea44cb1a901a5f57b207f60)](https://www.codacy.com/gh/mirko-pagliai/me-cms/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=mirko-pagliai/me-cms&amp;utm_campaign=Badge_Grade)
[![CodeFactor](https://www.codefactor.io/repository/github/mirko-pagliai/me-cms/badge)](https://www.codefactor.io/repository/github/mirko-pagliai/me-cms)

This repository contains only the source code of `me-cms`.
See [cakephp-for-mecms](https://github.com/mirko-pagliai/cakephp-for-mecms).

## How to extract POT files
To extract POT files for this plugin, use the following command:
```bash
$ composer run-script i18n-extract
```

## How to create migrations
To create migrations:
```bash
$ bin/cake bake migration_snapshot -f --require-table --no-lock --plugin MeCms Initial
```

## Testing
Tests are run for only one driver at a time, by default `mysql`.
To choose another driver to use, you can set the `driver_test` environment variable before running `phpunit`.

For example:
```
driver_test=postgres vendor/bin/phpunit
driver_test=sqlite vendor/bin/phpunit
```

Alternatively, you can set the `db_dsn` environment variable, indicating the connection parameters. In this case, the driver type will still be detected automatically.

For example:
```bash
db_dsn=sqlite:///' . TMP . 'example.sq3 vendor/bin/phpunit
```

## Versioning
For transparency and insight into our release cycle and to maintain backward compatibility,
Reflection will be maintained under the [Semantic Versioning guidelines](http://semver.org).
