<?php namespace BCDH\ExistDbRestClient;

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

}