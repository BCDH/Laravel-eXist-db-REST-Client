<?php

namespace BCDH\ExistDbRestClient;

use PHPUnit\Framework\TestCase;

class ExistDbRestClientTest extends TestCase {
    public function testSetPortUpdatesPortOption() {
        $client = new ExistDbRestClient([
            'port' => 8080,
        ]);

        $client->setPort(9090);

        $this->assertSame(9090, $client->getPort());
    }
}
