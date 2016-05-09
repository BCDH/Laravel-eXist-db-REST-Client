<?php namespace BCDH\ExistDbRestClient;

class XLSTransformationsTest extends SetupTest {
    public function testInsertData() {
        parent::insertData();
    }

    /**
     * @depends testInsertData
     */
    public function testSingleTransformation() {
        self::$connection->setHowMany(0);
        self::$connection->setWrap("no");

        $q = 'for $cd in /CD[./ARTIST=$artist] return $cd';

        $query = self::$connection->prepareQuery();
        $query->bindVariable('artist', 'Bonnie Tyler');
        $query->setCollection(self::$collectionName);
        $query->setQuery($q);

        $result = $query->get();
        $document = $result->getDocument();
        $singleCd = $document[0];

        $html = $result->transform(__DIR__ . '/xml/cd_catalog_simplified.xsl', $singleCd);
        $expected = $this->getExpectedXml($result->getDocument());

        $this->assertTrue(str_contains($html, $expected));
    }

    /**
     * @depends testInsertData
     */
    public function testGroupTransformation() {
        self::$connection->setHowMany(0);
        self::$connection->setWrap("no");

        $q = 'for $cd in /CD return $cd';

        $query = self::$connection->prepareQuery();
        $query->setCollection(self::$collectionName);
        $query->setQuery($q);

        $result = $query->get();
        $document = $result->getDocument();
        $rootTagName = '{}catalog';

        $html = $result->transform(__DIR__ . '/xml/cd_catalog_simplified.xsl', $document, $rootTagName);
        $expected = $this->getExpectedXml($document);

        $this->assertTrue(str_contains($html, $expected));
    }

    private function getExpectedXml($results) {
        $xml = '';
        foreach ($results as $r) {
            $title = $this->findArrayByValueName($r, "TITLE");
            $artist = $this->findArrayByValueName($r, "ARTIST");

            $xml .= '<tr>';
            $xml .= '<td>' . $title['value'] . '</td>';
            $xml .= '<td>' . $artist['value'] . '</td>';
            $xml .= '</tr>';
        }
        return $xml;
    }
}