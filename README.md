# Php dns-zone-tokenizer

[![Latest Stable Version](https://poser.pugx.org/ltd-beget/dns-zone-tokenizer/version)](https://packagist.org/packages/ltd-beget/dns-zone-tokenizer) 
[![Total Downloads](https://poser.pugx.org/ltd-beget/dns-zone-tokenizer/downloads)](https://packagist.org/packages/ltd-beget/dns-zone-tokenizer)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/LTD-Beget/dns-zone-tokenizer/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/LTD-Beget/dns-zone-tokenizer/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/LTD-Beget/dns-zone-tokenizer/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/LTD-Beget/dns-zone-tokenizer/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/LTD-Beget/dns-zone-tokenizer/badges/build.png?b=master)](https://scrutinizer-ci.com/g/LTD-Beget/dns-zone-tokenizer/build-status/master)
[![Documentation](https://img.shields.io/badge/code-documented-brightgreen.svg)](http://ltd-beget.github.io/dns-zone-tokenizer/documentation/html/index.html)
[![Documentation](https://img.shields.io/badge/code-coverage-brightgreen.svg)](http://ltd-beget.github.io/dns-zone-tokenizer/coverage/index.html)
[![License MIT](http://img.shields.io/badge/license-MIT-blue.svg?style=flat)](https://github.com/LTD-Beget/dns-zone-tokenizer/blob/master/LICENSE)



Tokenize dns zone files and that's all, folks.

## Installation

```shell
composer require ltd-beget/dns-zone-tokenizer
```

## Usage
```php
<?php
    use LTDBeget\dns\Tokenizer;
    
    require(__DIR__ . '/vendor/autoload.php');
    
    $config_path = realpath(__DIR__."/zone/exampleZone"); // path to your dns zone file
    $plain_config = file_get_contents($config_path);
    
    $tokenized = Tokenizer::tokenize($plain_config); // that's all, folks. All is done =)

```

## Developers

### Regenerate documentation
```shell
$ ./vendor/bin/phpdox
```

### Run tests

```shell
$ wget https://phar.phpunit.de/phpunit.phar
```

```shell
$ php phpunit.phar --coverage-html coverage
```

```shell
$ php phpunit.phar --coverage-clover coverage.xml
```

## License
released under the MIT License.
See the [bundled LICENSE file](LICENSE) for details.
