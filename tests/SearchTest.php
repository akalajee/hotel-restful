<?php

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;

class SearchTest extends TestCase {

    protected $_testMockResponseJsonArray = [];
    protected $_testCasesUriMap = [
        'testSearchAll' => '/v1/hotels',
        'testSearchAllSortByHotelAsc' => '/v1/hotels/hotel/1',
        'testSearchByHotelSortByHotelAsc' => '/v1/hotels/searchHotel/Con/hotel/1',
        'testSearchByPriceSortByPriceDesc' => '/v1/hotels/searchPrice/$80:$100/price/-1',
        'testSearchByDateSortByHotelDesc' => '/v1/hotels/searchDate/10-10-2020:11-10-2020/hotel/-1',
        'testSearchByCitySortByHotelAsc' => '/v1/hotels/searchCity/Dubai/hotel/1',
    ];

    public function setUp() {
        parent::setUp();
        $this->_testMockResponseJsonArray = require __DIR__ . DIRECTORY_SEPARATOR . 'TestMockData.php';
    }

    protected function generateExpectedDataPath($func) {
        return __DIR__ . DIRECTORY_SEPARATOR . 'ExpectedData' . str_replace("test", "", $func) . '.php';
    }

    protected function generateTest($func) {

        $mockResponseJsonString = json_encode($this->_testMockResponseJsonArray);

        // Create a mock and queue two responses.
        $mock = new MockHandler([
            new Response(200, [], $mockResponseJsonString),
        ]);

        // Creating a Hanlder Stack
        $handler = HandlerStack::create($mock);

        // Creating Gizzle Client with Mock Handler Stack
        $client = new Client(['handler' => $handler]);

        // Binding the object instance to Laravel IoC 
        $this->app->instance(\GuzzleHttp\Client::class, $client);

        $uri = $this->_testCasesUriMap[$func];

        $this->get($uri);

        $expectedData = require_once $this->generateExpectedDataPath($func);

        $this->seeJsonEquals($expectedData);
    }

    public function testSearchAll() {
        $this->generateTest(__FUNCTION__);
    }

    public function testSearchAllSortByHotelAsc() {
        $this->generateTest(__FUNCTION__);
    }

    public function testSearchByHotelSortByHotelAsc() {
        $this->generateTest(__FUNCTION__);
    }

    public function testSearchByPriceSortByPriceDesc() {
        $this->generateTest(__FUNCTION__);
    }

    public function testSearchByDateSortByHotelDesc() {
        $this->generateTest(__FUNCTION__);
    }

    public function testSearchByCitySortByHotelAsc() {
        $this->generateTest(__FUNCTION__);
    }

}
