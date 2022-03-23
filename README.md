# Modernyze

Modernyze is a package to help you easily connect to a Modernyze update server.

## Installation

```bash
composer require joonlabs/package-modernyze
```

## Example

```php
use Modernyze\Facades\Modernyze;

// set up the connection
Modernyze::setUrl("https://distribution.example.com")
    ->setSecret("MY_SECRET");
    ->setToken("MY_TOKEN");

// check all products
$products = Modernyze::allProducts();

// get the latest version of a product
$latest = Modernyze::latestVersion("my-product");

// get information about a specific product version
$information = Modernyze::versionInformation("my-product", "1.0.0");

// download, verify and install this product version
Modernyze::update("my-product", "1.0.0", app_path());

```


