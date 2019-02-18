# PHP Curl-HTTP requests methods

### Installation

To install PHP curl-http:

    composer require lisgroup/curl-http

### Requirements

Support PHP Version: 5.3+

### Quick Start and Examples

**NEW:** Get instances of class `Http`

```php
require __DIR__ . '/vendor/autoload.php';

use \Curl\Http;

// Get Class `Http` instances
$http = Http::getInstent(); // Old Version: $http = new Http();

$result = $http->request('https://www.example.com/');
var_dump($result);
```

GET Methods.

```php
// https://www.example.com/search?key=keyword
$curl = Http::getInstent(); // Old Version: $curl = new Http();
$result = $curl->get('https://www.example.com/search', array(
    'key' => 'keyword',
));
var_dump($result);
```

POST Methods.

```php
// https://www.example.com/login
$curl = Http::getInstent(); // Old Version: $curl = new Http();
$curl->post('https://www.example.com/login', array(
    'username' => 'myusername',
    'password' => 'mypassword',
));
```
