<?php namespace BCDH\ExistDbRestClient;

class VariableBindingTest extends SetupTest {
    public function testInsertData() {
        parent::insertData();
    }

    /**
     * @depends testInsertData
     */
    public function testBinding() {
        $q = 'for $cd in /CD[./PRICE < $price] return $cd';

        $query = self::$connection->prepareQuery();
        $query->setQuery($q);
        $query->bindVariable("price", 8.70);

        $formattedQuery = $query->getFormattedQuery();
        $expected = 'for $cd in /CD[./PRICE < 8.7] return $cd';

        $this->assertEquals($expected, $formattedQuery);
    }
}