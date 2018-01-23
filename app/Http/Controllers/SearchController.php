<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class SearchController extends BaseController {

    /**
     *
     * @var \App\Hotel
     */
    protected $_hotel;

    

    public function __construct(\GuzzleHttp\Client $client) {
        $this->_hotel = new \App\Hotel($client);
    }

    /**
     *
     * @param  int  $id
     * @return Response
     */
    public function find(string $searchHotel = null, string $searchCity = null, string $searchPrice = null, string $searchDate = null, string $sortKey = 'hotel', int $sortDir = \App\Hotel::SORT_DIR_ASC): array {
        $searchParams = [
            "searchHotel" => $searchHotel,
            "searchCity" => $searchCity,
            "searchPrice" => $searchPrice,
            "searchDate" => $searchDate,
        ];
        $allParams = [
            "sortKey" => $sortKey,
            "sortDir" => $sortDir
                ] + $searchParams;

        if ($this->_hotel->validateParameters($allParams)) {
            $hotelsJson = $this->_hotel->fetchHotels();

            $filteredHotels = $this->_hotel->searchHotels($hotelsJson, $searchParams);
            $sortedFilteredHotels = $this->_hotel->sortHotels($filteredHotels, $sortKey, $sortDir);
            return $this->prepareResponse($sortedFilteredHotels);
        }
    }

    protected function prepareResponse(array $hotels): array {
        return ["hotels" => $hotels];
    }

    

}
