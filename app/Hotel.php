<?php

namespace App;

use App\Exceptions\ParameterException as ParameterException;

class Hotel {

    /**
     *
     * @var \GuzzleHttp\Client
     */
    protected $_client;

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

    private function validateSearchHotel($searchHotel): void {
        if (!is_null($searchHotel) && !is_string($searchHotel)) {
            throw new ParameterException("searchHotel param Error!!");
        }
    }

    private function validateSearchCity($searchCity): void {
        if (!is_null($searchCity) && !is_string($searchCity)) {
            throw new ParameterException("searchCity param Error!!");
        }
    }

    private function validateSearchPrice($searchPrice): void {
        if (!is_null($searchPrice)) {
            $price_range_array = explode(":", $searchPrice);
            if (count($price_range_array) < 2) {
                throw new ParameterException("searchPrice param do not contain upper and lower bound Error!!");
            }
            if (strstr($price_range_array[0], "$") === false || strstr($price_range_array[1], "$") === false || !is_numeric(substr($price_range_array[0], 1)) || !is_numeric(substr($price_range_array[1], 1))) {
                throw new ParameterException("searchPrice param - lower or upper bound do not contain '$' or is not valid currency Error!!");
            }
        }
    }

    private function validateSearchDate($searchDate): void {
        if (!is_null($searchDate)) {
            $date_range_array = explode(":", $searchDate);
            if (count($date_range_array) < 2) {
                throw new ParameterException("searchDate param do not contain upper and lower bound Error!!");
            }
            if (strtotime($date_range_array[0]) === false || strtotime($date_range_array[1]) === false) {
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
        $searchHotel = $params["searchHotel"];
        $searchCity = $params["searchCity"];
        $searchPrice = $params["searchPrice"];
        $searchDate = $params["searchDate"];
        $sortKey = $params["sortKey"];
        $sortDir = $params["sortDir"];
        $this->validateSearchHotel($searchHotel);
        $this->validateSearchCity($searchCity);
        $this->validateSearchPrice($searchPrice);
        $this->validateSearchDate($searchDate);
        $this->validateSortKey($sortKey);
        $this->validateSortDir($sortDir);
        return true;
    }

    public function fetchHotels(): array {
        $res = $this->_client->get('https://api.myjson.com/bins/tl0bp');
        $hotels_string = $res->getBody();
        $hotels_array = \GuzzleHttp\json_decode($hotels_string, true);
        return $hotels_array["hotels"];
    }

    public function sortHotels(array $hotels, string $sortKey = 'hotel', int $sortDir = self::SORT_DIR_ASC): array {
        if (count($hotels) < 1)
            return [];
        $actual_sortKey = $sortKey;
        if (array_key_exists($sortKey, $this->_sortKeyMap)) {
            $actual_sortKey = $this->_sortKeyMap[$sortKey];
        }
        return $this->mergeSort($hotels, $actual_sortKey, $sortDir);
    }

    private function matchHotelPrice($hotel_value, $searchPrice): bool {
        $match_price = true;
        if (!empty($searchPrice) && strpos($searchPrice, ":") !== false) {
            $price_range_array = explode(":", $searchPrice);
            $min_searchPrice = substr($price_range_array[0], 1); // remove the currency sign ($)
            $max_searchPrice = substr($price_range_array[1], 1); // remove the currency sign ($)
            $match_price = ($hotel_value["price"] >= $min_searchPrice && $hotel_value["price"] <= $max_searchPrice);
        }
        return $match_price;
    }

    private function matchHotelDate($hotel_value, $searchDate): bool {
        $match_date = true;
        if (!empty($searchDate) && strpos($searchDate, ":") !== false) {
            $match_date = false;
            $date_range_array = explode(":", $searchDate);
            $availability_array = $hotel_value["availability"];
            foreach ($availability_array as $availability_row) {
                if (strtotime($date_range_array[0]) >= strtotime($availability_row["from"]) && strtotime($date_range_array[1]) <= strtotime($availability_row["to"])) {
                    $match_date = true;
                    break;
                }
            }
        }
        return $match_date;
    }

    public function searchHotels(array $hotels_array, array $search_params): array {

        $searchHotel = $search_params["searchHotel"];
        $searchCity = $search_params["searchCity"];
        $searchPrice = $search_params["searchPrice"];
        $searchDate = $search_params["searchDate"];

        $filtered_hotels = [];
        foreach ($hotels_array as $hotel_value) {
            $match_price = $this->matchHotelPrice($hotel_value, $searchPrice);
            $match_date = $this->matchHotelDate($hotel_value, $searchDate);

            $match_hotel = (empty($searchHotel) || (stripos($hotel_value["name"], $searchHotel) !== false));
            $match_city = (empty($searchCity) || (stripos($hotel_value["city"], $searchCity) !== false));


            if ($match_hotel && $match_city && $match_price && $match_date)
                $filtered_hotels[] = $hotel_value;
        }

        return $filtered_hotels;
    }

    /**
     * Sort the given array using merge-sort algorithm
     */
    protected function mergeSort($my_array, $sortKey, $sortDir): array {
        if (count($my_array) == 1)
            return $my_array;
        $mid = count($my_array) / 2;
        $left = array_slice($my_array, 0, $mid);
        $right = array_slice($my_array, $mid);
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

}
