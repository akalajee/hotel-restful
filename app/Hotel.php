<?php

namespace App;

use App\Exceptions\ParameterException as ParameterException;

class Hotel {

    /**
     *
     * @var \GuzzleHttp\Client
     */
    protected $_client;
    protected $_hotels;

    const SORT_DIR_ASC = 1;
    const SORT_DIR_DESC = -1;

    protected $_sortKeyMap = [
        "hotel" => "name",
        "price" => "price"
    ];
    protected $_sortDirMap = [
        self::SORT_DIR_ASC => 'ASC',
        self::SORT_DIR_DESC => 'DESC',
    ];

    public function __construct(\GuzzleHttp\Client $client) {
        $this->_client = $client;
    }

    private function validateSearchPrice($searchPrice): void {
        if (!is_null($searchPrice)) {
            $priceRangeArray = explode(":", $searchPrice);
            if (count($priceRangeArray) < 2) {
                throw new ParameterException("searchPrice param do not contain upper and lower bound Error!!");
            }
            if (strstr($priceRangeArray[0], "$") === false || strstr($priceRangeArray[1], "$") === false || !is_numeric(substr($priceRangeArray[0], 1)) || !is_numeric(substr($priceRangeArray[1], 1))) {
                throw new ParameterException("searchPrice param - lower or upper bound do not contain '$' or is not valid currency Error!!");
            }
        }
    }

    private function validateSearchDate($searchDate): void {
        if (!is_null($searchDate)) {
            $dateRangeArray = explode(":", $searchDate);
            if (count($dateRangeArray) < 2) {
                throw new ParameterException("searchDate param do not contain upper and lower bound Error!!");
            }
            if (strtotime($dateRangeArray[0]) === false || strtotime($dateRangeArray[1]) === false) {
                throw new ParameterException("searchDate param - lower or upper bound is not valid date");
            }
        }
    }

    private function validateSortKey($sortKey): void {
        if (!is_null($sortKey) && !is_string($sortKey)) {
            throw new ParameterException("sortKey param Error!!");
        } elseif (!is_null($sortKey)) {
            if (!array_key_exists($sortKey, $this->_sortKeyMap)) {
                throw new ParameterException("sortKey param - invalid sort key option");
            }
        }
    }

    private function validateSortDir($sortDir): void {
        if (!empty($sortDir) && !is_int($sortDir)) {
            throw new ParameterException("sortDir param Error!!");
        } elseif (!is_null($sortDir)) {
            if (!array_key_exists($sortDir, $this->_sortDirMap)) {
                throw new ParameterException("sortDir param - invalid sort dir option");
            }
        }
    }

    public function validateParameters($params): bool {
        $searchPrice = $params["searchPrice"];
        $searchDate = $params["searchDate"];
        $sortKey = $params["sortKey"];
        $sortDir = $params["sortDir"];
        $this->validateSearchPrice($searchPrice);
        $this->validateSearchDate($searchDate);
        $this->validateSortKey($sortKey);
        $this->validateSortDir($sortDir);
        return true;
    }

    public function fetchHotels(): void {
        $res = $this->_client->get('https://api.myjson.com/bins/tl0bp');
        $hotelsString = $res->getBody();
        $hotelsArray = \GuzzleHttp\json_decode($hotelsString, true);
        $this->_hotels = $hotelsArray["hotels"];
    }

    public function sortHotels(string $sortKey = 'hotel', int $sortDir = self::SORT_DIR_ASC): array {
        if (count($this->_hotels) < 1)
            return [];
        $actualSortKey = $sortKey;
        if (array_key_exists($sortKey, $this->_sortKeyMap)) {
            $actualSortKey = $this->_sortKeyMap[$sortKey];
        }
        return $this->_hotels = $this->mergeSort($this->_hotels, $actualSortKey, $sortDir);
    }

    private function matchHotelPrice($hotelValue, $searchPrice): bool {
        $matchPrice = true;
        if (!empty($searchPrice) && strpos($searchPrice, ":") !== false) {
            $priceRangeArray = explode(":", $searchPrice);
            $minSearchPrice = substr($priceRangeArray[0], 1); // remove the currency sign ($)
            $maxSearchPrice = substr($priceRangeArray[1], 1); // remove the currency sign ($)
            $matchPrice = ($hotelValue["price"] >= $minSearchPrice && $hotelValue["price"] <= $maxSearchPrice);
        }
        return $matchPrice;
    }

    private function matchHotelDate($hotelValue, $searchDate): bool {
        $matchDate = true;
        if (!empty($searchDate) && strpos($searchDate, ":") !== false) {
            $matchDate = false;
            $dateRangeArray = explode(":", $searchDate);
            $availabilityArray = $hotelValue["availability"];
            foreach ($availabilityArray as $availabilityRow) {
                if (strtotime($dateRangeArray[0]) >= strtotime($availabilityRow["from"]) && strtotime($dateRangeArray[1]) <= strtotime($availabilityRow["to"])) {
                    $matchDate = true;
                    break;
                }
            }
        }
        return $matchDate;
    }

    public function filterHotels(array $searchParams): array {

        $searchHotel = $searchParams["searchHotel"];
        $searchCity = $searchParams["searchCity"];
        $searchPrice = $searchParams["searchPrice"];
        $searchDate = $searchParams["searchDate"];

        $filteredHotels = [];
        foreach ($this->_hotels as $hotelValue) {
            $matchPrice = $this->matchHotelPrice($hotelValue, $searchPrice);
            $matchDate = $this->matchHotelDate($hotelValue, $searchDate);

            $matchHotel = (empty($searchHotel) || (stripos($hotelValue["name"], $searchHotel) !== false));
            $matchCity = (empty($searchCity) || (stripos($hotelValue["city"], $searchCity) !== false));


            if ($matchHotel && $matchCity && $matchPrice && $matchDate)
                $filteredHotels[] = $hotelValue;
        }

        return $this->_hotels = $filteredHotels;
    }

    /**
     * Sort the given array using merge-sort algorithm
     */
    protected function mergeSort($myArray, $sortKey, $sortDir): array {
        if (count($myArray) == 1)
            return $myArray;
        $mid = count($myArray) / 2;
        $left = array_slice($myArray, 0, $mid);
        $right = array_slice($myArray, $mid);
        $left = $this->mergeSort($left, $sortKey, $sortDir);
        $right = $this->mergeSort($right, $sortKey, $sortDir);
        return $this->merge($left, $right, $sortKey, $sortDir);
    }

    private function merge($left, $right, $sortKey, $sortDir): array {
        $res = [];
        $i = 0;
        while (count($left) > 0 && count($right) > 0) {
            if ($sortDir == self::SORT_DIR_ASC) {
                if ($left[0][$sortKey] > $right[0][$sortKey]) {
                    $res[] = $right[0];
                    $right = array_slice($right, 1);
                } else {
                    $res[] = $left[0];
                    $left = array_slice($left, 1);
                }
            } elseif ($sortDir == self::SORT_DIR_DESC) {
                if ($left[0][$sortKey] < $right[0][$sortKey]) {
                    $res[] = $right[0];
                    $right = array_slice($right, 1);
                } else {
                    $res[] = $left[0];
                    $left = array_slice($left, 1);
                }
            }
            $i++;
        }
        while (count($left) > 0) {
            $res[] = $left[0];
            $left = array_slice($left, 1);
        }
        while (count($right) > 0) {
            $res[] = $right[0];
            $right = array_slice($right, 1);
        }
        return $res;
    }
    
    public function getHotels() : array
    {
        return $this->_hotels;
    }

}
