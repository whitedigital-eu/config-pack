# config-pack

Installation
---

```shell
composer require --dev whitedigital-eu/config-pack
```

Usage
---
### PhpCsFixer:

```shell
vendor/bin/php-cs-fixer --config=vendor/whitedigital-eu/config-pack/.php-cs-fixer.php fix
```
In PhpStorm:  
* point to same config file in settings of PHP CS Fixer
* check "allow risky rules" checkbox
* choose custom ruleset
* point to php-cs-fixer binary

### PhpUnit:
```shell
vendor/bin/phpunit --log-junit report.xml --configuration vendor/whitedigital-eu/config-pack/phpunit.xml.dist tests
```
*or if tests/bootstrap.php differs from one in whitedigital-eu/config-pack:*
```shell
vendor/bin/phpunit --log-junit report.xml --configuration vendor/whitedigital-eu/config-pack/phpunit.xml.dist --bootstrap=tests/bootstrap.php tests
```
### Simple PhpUnit:
```shell
bin/phpunit --log-junit report.xml --configuration vendor/whitedigital-eu/config-pack/phpunit.xml.dist tests
```
*or if tests/bootstrap.php differs from one in whitedigital-eu/config-pack:*
```shell
bin/phpunit --log-junit report.xml --configuration vendor/whitedigital-eu/config-pack/phpunit.xml.dist --bootstrap=tests/bootstrap.php tests
```
### PhpStan
```shell
vendor/bin/phpstan --configuration=vendor/whitedigital-eu/config-pack/phpstan.neon.dist analyse src
```
In PhpStorm:
* point to same config file in settings of PHPStan
* point to your project's autoload, usually `vendor/autoload.php`
* point to phpstan binary
