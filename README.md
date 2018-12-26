# MeCms

[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.txt)
[![Build Status](https://travis-ci.org/mirko-pagliai/me-cms.svg?branch=master)](https://travis-ci.org/mirko-pagliai/me-cms)
[![Coverage Status](https://img.shields.io/codecov/c/github/mirko-pagliai/me-cms.svg?style=flat-square)](https://codecov.io/github/mirko-pagliai/me-cms)

This repository contains only the source code of MeCms.  
See [cakephp-for-mecms](https://github.com/mirko-pagliai/cakephp-for-mecms).

## Tests
Tests are divided into two groups, `onlyUnix` and `onlyWindows`. This is
necessary because some commands to be executed in the terminal are only valid
for an environment.

By default, phpunit is executed like this:

    vendor/bin/phpunit --exclude-group=onlyWindows

On Windows, it must be done this way:

    vendor\bin\phpunit.bat --exclude-group=onlyUnix

## Versioning
For transparency and insight into our release cycle and to maintain backward compatibility, 
Reflection will be maintained under the [Semantic Versioning guidelines](http://semver.org).
