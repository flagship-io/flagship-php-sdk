## Coverage
[![CI PHP 5.4](https://github.com/flagship-io/flagship-php-sdk-dev/actions/workflows/CI_PHP_5_4.yml/badge.svg)](https://github.com/flagship-io/flagship-php-sdk-dev/actions/workflows/CI_PHP_5_4.yml) 
[![CI PHP 5.6](https://github.com/flagship-io/flagship-php-sdk-dev/actions/workflows/CI_PHP_5_6.yml/badge.svg)](https://github.com/flagship-io/flagship-php-sdk-dev/actions/workflows/CI_PHP_5_6.yml) 
[![CI PHP 7.4](https://github.com/flagship-io/flagship-php-sdk-dev/actions/workflows/CI_PHP_7.4.yml/badge.svg)](https://github.com/flagship-io/flagship-php-sdk-dev/actions/workflows/CI_PHP_7.4.yml) 
[![CI PHP 8](https://github.com/flagship-io/flagship-php-sdk-dev/actions/workflows/CI_PHP_8.yml/badge.svg)](https://github.com/flagship-io/flagship-php-sdk-dev/actions/workflows/CI_PHP_8.yml) \
**PHP 5.4** ![Code Coverage Badge](./badge_php_5_4.svg) 
**PHP 5.6** ![ Code Coverage Badge](./badge_php_5_6.svg) 
**PHP 7.4** ![ Code Coverage Badge](./badge_php_7_4.svg) 
**PHP 8** ![ Code Coverage Badge](./badge_php_8.svg)
## Usage
```php
require __dir__ . '/vendor/autoload.php';

use Flagship\Flagship;

Flagship::start('envId', 'apiKey');

$visitor = Flagship::newVisitor('visitorId',['contextKey'=>'contextValue']);

if ($visitor) {

    $visitor->synchronizedModifications();

    echo $visitor->getModification('key', 'defaultValue') . "\n";
}
```