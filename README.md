# Laravel eXist-db REST Client

A small Laravel-friendly client for querying eXist-db over its REST API, parsing XML responses, and applying XSLT transformations.

## Version compatibility

- `1.1.x`: Laravel 8
- `1.2.x` (`v1.2.0`): Laravel 9
- `1.3.x`: planned for Laravel 10
- `2.x`: planned for Laravel 11+

The latest released bridge line is `1.2.x` for Laravel 9.

The default branch, `master`, is the development branch. Use a tagged release when installing the package in an application.

## Requirements

Current `master` branch requirements:

- PHP `^7.2|^8.0`
- Laravel / `illuminate/support` `^5.5|^6.0|^7.0|^8.0`
- PHP XSL extension for XSLT transformations
- An accessible eXist-db REST endpoint

## Installation

Install the version that matches your Laravel line:

```bash
composer require bcdh/exist-db-rest-client:^1.2   # Laravel 9
```

For Laravel 8, use:

```bash
composer require bcdh/exist-db-rest-client:^1.1
```

Laravel package discovery will register the service provider automatically.

If you are integrating the package into an older Laravel application without package discovery, register the provider manually in `config/app.php`:

```php
BCDH\ExistDbRestClient\ExistDbServiceProvider::class,
```

Publish the package configuration:

```bash
php artisan vendor:publish --provider="BCDH\ExistDbRestClient\ExistDbServiceProvider"
```

Then adjust `config/exist-db.php`:

```php
return [
    'user' => 'admin',
    'password' => 'admin',

    'protocol' => 'http',
    'host' => 'localhost',
    'port' => 8080,
    'path' => 'exist/rest',

    // Alternatively, provide the full base URI.
    // 'uri' => 'http://localhost:8080/exist/rest/',

    'xsl' => 'no',
    'indent' => 'yes',
    'howMany' => 10,
    'start' => 1,
    'wrap' => 'yes',
];
```

## Basic usage

```php
use BCDH\ExistDbRestClient\ExistDbRestClient;

$xquery = 'for $cd in /CD[./ARTIST = $artist] return $cd';

$client = new ExistDbRestClient();
$query = $client->prepareQuery();

$query->setCollection('CDCatalog');
$query->setQuery($xquery);
$query->bindVariable('artist', 'Bonnie Tyler');

$result = $query->get();
$document = $result->getDocument();
```

`getDocument()` returns the parsed XML result. `getRawResult()` returns the raw XML string from eXist-db.

## Using the client outside Laravel

You can also pass configuration directly:

```php
use BCDH\ExistDbRestClient\ExistDbRestClient;

$client = new ExistDbRestClient([
    'uri' => 'http://localhost:8080/exist/rest/',
    'user' => 'admin',
    'password' => 'admin',
    'xsl' => 'no',
    'indent' => 'yes',
    'howMany' => 0,
    'start' => 1,
    'wrap' => 'yes',
]);
```

## Query helpers

`Query` supports:

- `setQuery($xquery)` for inline XQuery
- `setStoredQuery($path)` for stored XQuery resources
- `setCollection($collection)`
- `setResource($resource)`
- `bindVariable($name, $value)` for XQuery variable substitution
- `bindParam($name, $value)` for request parameters
- `setBody($body)` for request payloads
- `get()`, `post()`, `put()`, and `delete()`

Example with a stored query and request parameter:

```php
$query = $client->prepareQuery();
$query->setStoredQuery('cd.xql');
$query->bindParam('price', 7.9);

$result = $query->get();
```

Example uploading XML into a collection:

```php
$query = $client->prepareQuery();
$query->setCollection('CDCatalog');
$query->setResource('new-record.xml');
$query->setBody($xml);

$query->put();
```

## Result parsing

Results are parsed with [`sabre/xml`](http://sabre.io/xml/reading/). You can pass your own `Sabre\Xml\Service` instance to any request method:

```php
use Sabre\Xml\Service;

$service = new Service();

$result = $query->get($service);
$document = $result->getDocument();
```

Typical parsed output looks like this:

```php
[
    [
        'name' => '{}CD',
        'value' => [
            [
                'name' => '{}TITLE',
                'value' => 'Empire Burlesque',
                'attributes' => [],
            ],
            [
                'name' => '{}ARTIST',
                'value' => 'Bob Dylan',
                'attributes' => [],
            ],
        ],
        'attributes' => [
            'favourite' => '1',
        ],
    ],
]
```

## XSLT transformations

`XMLResult::transform()` applies an XSL stylesheet to either the full document or a selected fragment.

Transform a single result node:

```php
$result = $query->get();
$document = $result->getDocument();
$singleCd = $document[0];

$html = $result->transform(__DIR__ . '/xml/cd_catalog_simplified.xsl', $singleCd);
```

Transform a collection of nodes with a custom root tag:

```php
$result = $query->get();
$document = $result->getDocument();

$html = $result->transform(
    __DIR__ . '/xml/cd_catalog_simplified.xsl',
    $document,
    '{}catalog'
);
```

## Running tests

The test suite expects a local eXist-db instance at `http://localhost:8080/exist/rest/` with the default `admin/admin` credentials.

Run:

```bash
composer test
```
