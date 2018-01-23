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
        $search_params = [
            "searchHotel" => $searchHotel,
            "searchCity" => $searchCity,
            "searchPrice" => $searchPrice,
            "searchDate" => $searchDate,
        ];
        $all_params = [
            "sortKey" => $sortKey,
            "sortDir" => $sortDir
                ] + $search_params;

        if ($this->_hotel->validateParameters($all_params)) {
            $hotels_json = $this->_hotel->fetchHotels();

            $filtered_hotels = $this->_hotel->searchHotels($hotels_json, $search_params);
            $sorted_filtered_hotels = $this->_hotel->sortHotels($filtered_hotels, $sortKey, $sortDir);
            return $this->prepareResponse($sorted_filtered_hotels);
        }
    }

    protected function prepareResponse(array $hotels): array {
        return ["hotels" => $hotels];
    }

    

}
