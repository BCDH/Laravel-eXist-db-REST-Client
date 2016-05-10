Laravel eXist-db REST Client
=============================

A Laravel client for querying and transforming results from eXist-db via REST API.

##  Requirements:

- Laravel 5
- PHP 5.5
- PHP XSLT extension
```bash
sudo apt-get install php5-xsl
```

## Installing

####1. Add the service provider to your config/app.php:

    BCDH\ExistDbRestClient\ExistDbServiceProvider::class

####2. Publish your configuration file:
    
    php artisan vendor:publish
    
####3. Edit your connection credentials in `config/exist-db.php`

    [
        'user'          => 'admin',
        'password'      => 'admin',
    
        'protocol'      => 'http',
        'host'          => 'localhost',
        'port'          => 8080,
        'path'          => 'exist/rest',
    
        /* alternatively, you can specify the URI as a whole in the form */
        // 'uri'=>'http://localhost:8080/exist/rest/'
    
        'xsl'           => 'no',
        'indent'        => 'yes',
        'howMany'       => 10,
        'start'         => 1,
        'wrap'          => 'yes'
    ]
    

## Usage 

```php
use BCDH\ExistDbRestClient\ExistDbRestClient;

$q = 'for $cd in /CD[./ARTIST=$artist] return $cd';

$query = self::$connection->prepareQuery();
$query->bindVariable('artist', 'Bonnie Tyler');
$query->setCollection(self::$collectionName);
$query->setQuery($q);

$result = $query->get();
$document = $result->getDocument();
```

#### Result formatting

[sabre/xml](http://sabre.io/xml/reading/) library is used for parsing xml result.
You can pass an instance of \Sabre\Xml\Service with your own (de)serializers to Query request methods

#### Result example

```php
array(
    array(
        'name' => '{}CD',
        'value' => array(
            0 => array(
                'name' => '{}TITLE',
                'value' => 'Empire Burlesque',
                'attributes' => array(),
            ),
            1 => array(
                'name' => '{}ARTIST',
                'value' => 'Bob Dylan',
                'attributes' => array(),
            ),
            2 => array(
                'name' => '{}COUNTRY',
                'value' => 'USA',
                'attributes' => array(),
            ),
            3 => array(
                'name' => '{}COMPANY',
                'value' => 'Columbia',
                'attributes' => array(),
            )
        ),
        'attributes' =>  array (
            'favourite' => '1',
        ),
    ),
);
```

## XLS transformations

- Single result

```php
$result = $query->get();
$document = $result->getDocument();
$singleCd = $document[0];

$html = $result->transform(__DIR__ . '/xml/cd_catalog_simplified.xsl', $singleCd);
```

- Result

```php
$result = $query->get();
$document = $result->getDocument();
$rootTagName = '{}catalog';

$html = $result->transform(__DIR__ . '/xml/cd_catalog_simplified.xsl', $document, $rootTagName);
```