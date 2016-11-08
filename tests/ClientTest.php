<?php namespace BCDH\ExistDbRestClient;

use Sabre\Xml\Reader;
use Sabre\Xml\Service;

class ClientTest extends SetupTest {
    public function testInsertData() {
        parent::insertData();
    }

    /**
     * @depends testInsertData
     */
    public function testWhereQuery() {
        self::$connection->setHowMany(0);
        self::$connection->setWrap("no");

        $xql = 'for $cd in /CD[./PRICE < $price] return $cd';

        /** @var Query $query */
        $query = self::$connection->prepareQuery();
        $query->setQuery($xql);
        $query->setCollection(self::$collectionName);
        $query->bindVariable("price", 8.70);

        $results = $query->get()->getDocument();

        $count = count($results);
        $expected = 10;

        $this->assertEquals($expected, $count);
    }

    private function storeQuery() {
        $xql = 'xquery version "1.0"; ' . PHP_EOL .
            'let $price := xs:double(request:get-parameter("price", ()))' . PHP_EOL .
            'for $cd in /CD[./PRICE < $price] return $cd';

        $query = self::$connection->prepareQuery();
        $query->setBody($xql);
        $query->setResource('cd.xql');
        $query->setBinaryContent(false);
        $query->put();
    }

    /**
     * @depends testInsertData
     */
    public function testStoredQuery() {
        self::$connection->setHowMany(0);
        self::$connection->setWrap("no");

        $this->storeQuery();

        $xql = 'cd.xql';

        /** @var Query $query */
        $query = self::$connection->prepareQuery();
        $query->setStoredQuery($xql);
        $query->bindParam("price", 7.9);

        $results = $query->get()->getDocument();

        $count = count($results);
        $expected = 3;

        $this->assertEquals($expected, $count);
    }

    /**
     * @depends testInsertData
     */
    public function testWhereQueryWrongTypeConversion() {
        self::$connection->setHowMany(0);
        self::$connection->setWrap("no");

        $xql = 'for $cd in /CD[./PRICE < $price] return $cd';

        /** @var Query $query */
        $query = self::$connection->prepareQuery();
        $query->setQuery($xql);
        $query->setCollection(self::$collectionName);
        $query->bindVariable('price', "8.70");

        $results = $query->get()->getDocument();

        $count = count($results);
        $expected = 16;

        $this->assertEquals($expected, $count);
    }

    /**
     * @depends testInsertData
     */
    public function testWhereQueryEquals() {
        self::$connection->setHowMany(0);
        self::$connection->setWrap("no");

        $xql = 'for $cd in /CD[./ARTIST=$artist] return $cd';

        $query = self::$connection->prepareQuery();
        $query->setQuery($xql);
        $query->setCollection(self::$collectionName);
        $query->bindVariable('artist', 'Eros Ramazzotti');

        $result = $query->get()->getDocument();

        $count = count($result);

        if ($count != 1) {
            $this->assertTrue(false, "Wrong entries found: $count");
        }

        $expectedPrice = "9.90";
        $cd = $result[0];
        $price = $this->findArrayByValueName($cd, "PRICE");

        $this->assertEquals($expectedPrice, $price['value']);
    }

    /**
     * @depends testInsertData
     */
    public function testAttributeNegative() {
        self::$connection->setHowMany(0);
        self::$connection->setWrap("no");

        $xql = 'for $cd in /CD[./ARTIST=$artist] return $cd';

        $query = self::$connection->prepareQuery();
        $query->setQuery($xql);
        $query->setCollection(self::$collectionName);
        $query->bindVariable('artist', 'Bob Dylan');

        $result = $query->get()->getDocument();

        $count = count($result);

        if ($count != 1) {
            $this->assertTrue(false, "Wrong entries found: $count");
        }

        $xml = $result[0];
        $attributes = $xml['attributes'];

        $this->assertFalse(isset($attributes['favourite']) && $attributes['favourite']);
    }

    /**
     * @depends testInsertData
     */
    public function testAttribute() {
        self::$connection->setHowMany(0);
        self::$connection->setWrap("no");

        $xql = 'for $cd in /CD[./ARTIST=$artist] return $cd';

        $query = self::$connection->prepareQuery();
        $query->setQuery($xql);
        $query->setCollection(self::$collectionName);
        $query->bindVariable('artist', 'Bonnie Tyler');

        $result = $query->get()->getDocument();
        $firstCd = $result[0];

        $hasAttribute = isset($firstCd['attributes']['favourite']);
        $this->assertTrue($hasAttribute);

        $isFavorite = isset($firstCd['attributes']['favourite']);
        $this->assertEquals('1', $isFavorite);
    }

    /**
     * @depends testInsertData
     */
    public function testSabreParser() {
        self::$connection->setHowMany(0);
        self::$connection->setWrap("no");

        $xql = 'for $cd in /CD[./ARTIST=$artist] return $cd';

        $query = self::$connection->prepareQuery();
        $query->setQuery($xql);
        $query->setCollection(self::$collectionName);
        $query->bindVariable('artist', 'Bob Dylan');

        $service = $this->getSabreParser();

        $result = $query->get($service)->getDocument();

        $count = count($result);

        if ($count != 1) {
            $this->assertTrue(false, "Wrong entries found: $count");
        }

        /** @var CD $object */
        $object = $result[0]['value'];

        $this->assertEquals($object->artist, "Bob Dylan");
    }

    /**
     * @depends testInsertData
     */
    public function testSabreParserMultiple() {
        self::$connection->setHowMany(0);
        self::$connection->setWrap("no");

        $xql = 'for $cd in /CD[./PRICE < $price] return $cd';

        /** @var Query $query */
        $query = self::$connection->prepareQuery();
        $query->setQuery($xql);
        $query->setCollection(self::$collectionName);
        $query->bindVariable("price", 8.70);

        $service = $this->getSabreParser();

        $results = $query->get($service)->getDocument();

        $count = count($results);
        $expected = 10;

        $this->assertEquals($expected, $count);

        /** @var CD $object */
        $object = $results[5]['value'];

        $this->assertEquals($object->artist, "The Communards");
    }

    private function getSabreParser() {
        $service = new Service();
        $service->elementMap = [
            '{}' => function(Reader $reader) {
                return \Sabre\Xml\Deserializer\repeatingElements($reader, '{}CD');
            },
            '{}CD' => function($reader) {
                $cd = new CD();
                // Borrowing a parser from the KeyValue class.
                $keyValue = \Sabre\Xml\Deserializer\keyValue($reader, '');

                if (isset($keyValue['TITLE'])) {
                    $cd->title = $keyValue['TITLE'];
                }
                if (isset($keyValue['ARTIST'])) {
                    $cd->artist = $keyValue['ARTIST'];
                }
                if (isset($keyValue['COUNTRY'])) {
                    $cd->country = $keyValue['COUNTRY'];
                }
                if (isset($keyValue['COMPANY'])) {
                    $cd->company = $keyValue['COMPANY'];
                }
                if (isset($keyValue['PRICE'])) {
                    $cd->price = $keyValue['PRICE'];
                }
                if (isset($keyValue['YEAR'])) {
                    $cd->year = $keyValue['YEAR'];
                }

                return $cd;
            },
        ];

        return $service;
    }
}

class CD {
    public $title;
    public $artist;
    public $country;
    public $company;
    public $price;
    public $year;
}