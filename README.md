# Php dns-zone-tokenizer

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
    
    $config_path = realpath(__DIR__."/zone/exampleZone"); // path to your sphinx conf
    $plain_config = file_get_contents($config_path); // or some string with sphinx conf
    
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
