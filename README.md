# PHP Curl-HTTP requests methods

### Installation

To install PHP curl-http:

    composer require lisgroup/curl-http

### Requirements

Support PHP Version: 5.3+

### Quick Start and Examples

```php
require __DIR__ . '/vendor/autoload.php';

use \Curl\Http;

$http = new Http();
$result = $http->request('https://www.example.com/', '', 'GET');
var_dump($result);
```
