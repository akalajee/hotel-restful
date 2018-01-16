<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class SearchController extends BaseController {

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

    /**
     *
     * @param  int  $id
     * @return Response
     */
    public function find(string $search_hotel = null, string $search_city = null, string $search_price = null, string $search_date = null, string $sort_key = 'hotel', int $sort_dir = self::SORT_DIR_ASC): array {
        $params = [
            "search_hotel" => $search_hotel,
            "search_city" => $search_city,
            "search_price" => $search_price,
            "search_date" => $search_date,
            "sort_key" => $sort_key,
            "sort_dir" => $sort_dir
        ];
        if ($this->validateParameters($params)) {
            $hotels_json = $this->fetchHotels();
            $search_params = [
                "search_hotel" => $search_hotel,
                "search_city" => $search_city,
                "search_price" => $search_price,
                "search_date" => $search_date,
            ];
            $filtered_hotels = $this->searchHotels($hotels_json, $search_params);
            $sorted_filtered_hotels = $this->sortHotels($filtered_hotels, $sort_key, $sort_dir);
            return $this->prepareResponse($sorted_filtered_hotels);
        }
    }

    protected function prepareResponse(array $hotels): array {
        return ["hotels" => $hotels];
    }

    private function validateSearchHotel($search_hotel): void {
        if (!is_null($search_hotel) && !is_string($search_hotel)) {
            throw new \Exception("search_hotel param Error!!");
        }
    }

    private function validateSearchCity($search_city): void {
        if (!is_null($search_city) && !is_string($search_city)) {
            throw new \Exception("search_city param Error!!");
        }
    }

    private function validateSearchPrice($search_price): void {
        if (!is_null($search_price)) {
            if (strstr($search_price, ":") === false) {
                throw new \Exception("search_price param do not contain ':' Error!!");
            }
            $price_range_array = explode(":", $search_price);
            if (count($price_range_array) < 2) {
                throw new \Exception("search_price param do not contain upper and lower bound Error!!");
            }
            if (strstr($price_range_array[0], "$") === false || strstr($price_range_array[1], "$") === false) {
                throw new \Exception("search_price param - lower or upper bound do not contain '$' Error!!");
            }
            if (!is_numeric(substr($price_range_array[0], 1)) || !is_numeric(substr($price_range_array[1], 1))) {
                throw new \Exception("search_price param - lower or upper bound is not valid currency Error!!");
            }
        }
    }

    private function validateSearchDate($search_date): void {
        if (!is_null($search_date)) {
            if (strstr($search_date, ":") === false) {
                throw new \Exception("search_date param do not contain ':' Error!!");
            }
            $date_range_array = explode(":", $search_date);
            if (count($date_range_array) < 2) {
                throw new \Exception("search_date param do not contain upper and lower bound Error!!");
            }
            if (strtotime($date_range_array[0]) === false) {
                throw new \Exception("search_date param - lower bound is not valid date");
            }
            if (strtotime($date_range_array[1]) === false) {
                throw new \Exception("search_date param - upper bound is not valid date");
            }
        }
    }

    private function validateSortKey($sort_key): void {
        if (!is_null($sort_key) && !is_string($sort_key)) {
            throw new \Exception("sort_key param Error!!");
        } elseif (!is_null($sort_key)) {
            if (!array_key_exists($sort_key, $this->_sortKeyMap)) {
                throw new \Exception("sort_key param - invalid sort key option");
            }
        }
    }

    private function validateSortDir($sort_dir): void {
        if (!empty($sort_dir) && !is_int($sort_dir)) {
            throw new \Exception("sort_dir param Error!!");
        } elseif (!is_null($sort_dir)) {
            if (!array_key_exists($sort_dir, $this->_sortDirMap)) {
                throw new \Exception("sort_dir param - invalid sort dir option");
            }
        }
    }

    protected function validateParameters($params): bool {
        $search_hotel = $params["search_hotel"];
        $search_city = $params["search_city"];
        $search_price = $params["search_price"];
        $search_date = $params["search_date"];
        $sort_key = $params["sort_key"];
        $sort_dir = $params["sort_dir"];
        $this->validateSearchHotel($search_hotel);
        $this->validateSearchCity($search_city);
        $this->validateSearchPrice($search_price);
        $this->validateSearchDate($search_date);
        $this->validateSortKey($sort_key);
        $this->validateSortDir($sort_dir);
        return true;
    }

    protected function fetchHotels(): array {
        $res = $this->_client->get('https://api.myjson.com/bins/tl0bp');
        $hotels_string = $res->getBody();
        $hotels_array = \GuzzleHttp\json_decode($hotels_string, true);
        return $hotels_array["hotels"];
    }

    protected function sortHotels(array $hotels, string $sort_key = 'hotel', int $sort_dir = self::SORT_DIR_ASC): array {
        if (count($hotels) < 1)
            return [];
        $actual_sort_key = $sort_key;
        if (array_key_exists($sort_key, $this->_sortKeyMap)) {
            $actual_sort_key = $this->_sortKeyMap[$sort_key];
        }
        return $this->mergeSort($hotels, $actual_sort_key, $sort_dir);
    }

    private function matchHotelPrice($hotel_value, $search_price): bool {
        $match_price = true;
        if (!empty($search_price) && strpos($search_price, ":") !== false) {
            $price_range_array = explode(":", $search_price);
            $min_search_price = substr($price_range_array[0], 1); // remove the currency sign ($)
            $max_search_price = substr($price_range_array[1], 1); // remove the currency sign ($)
            $match_price = ($hotel_value["price"] >= $min_search_price && $hotel_value["price"] <= $max_search_price);
        }
        return $match_price;
    }

    private function matchHotelDate($hotel_value, $search_date): bool {
        $match_date = true;
        if (!empty($search_date) && strpos($search_date, ":") !== false) {
            $match_date = false;
            $date_range_array = explode(":", $search_date);
            $search_from_date = strtotime($date_range_array[0]);
            $search_to_date = strtotime($date_range_array[1]);
            $availability_array = $hotel_value["availability"];
            if (is_array($availability_array) && count($availability_array) > 0) {
                foreach ($availability_array as $availability_row) {
                    $available_from = strtotime($availability_row["from"]);
                    $available_to = strtotime($availability_row["to"]);
                    if ($search_from_date >= $available_from && $search_to_date <= $available_to) {
                        $match_date = true;
                        break;
                    }
                }
            }
        }
        return $match_date;
    }

    protected function searchHotels(array $hotels_array, array $search_params): array {
        $search_hotel = $search_params["search_hotel"];
        $search_city = $search_params["search_city"];
        $search_price = $search_params["search_price"];
        $search_date = $search_params["search_date"];

        $filtered_hotels = [];
        foreach ($hotels_array as $hotel_value) {
            $match_price = $this->matchHotelPrice($hotel_value, $search_price);
            $match_date = $this->matchHotelDate($hotel_value, $search_date);

            $match_hotel = (empty($search_hotel) || (stripos($hotel_value["name"], $search_hotel) !== false));
            $match_city = (empty($search_city) || (stripos($hotel_value["city"], $search_city) !== false));


            if ($match_hotel && $match_city && $match_price && $match_date)
                $filtered_hotels[] = $hotel_value;
        }

        return $filtered_hotels;
    }

    /**
     * Sort the given array using merge-sort algorithm
     */
    protected function mergeSort($my_array, $sort_key, $sort_dir): array {
        if (count($my_array) == 1)
            return $my_array;
        $mid = count($my_array) / 2;
        $left = array_slice($my_array, 0, $mid);
        $right = array_slice($my_array, $mid);
        $left = $this->mergeSort($left, $sort_key, $sort_dir);
        $right = $this->mergeSort($right, $sort_key, $sort_dir);
        return $this->merge($left, $right, $sort_key, $sort_dir);
    }

    private function merge($left, $right, $sort_key, $sort_dir): array {
        $res = [];
        $i = 0;
        while (count($left) > 0 && count($right) > 0) {
            if ($sort_dir == self::SORT_DIR_ASC) {
                if ($left[0][$sort_key] > $right[0][$sort_key]) {
                    $res[] = $right[0];
                    $right = array_slice($right, 1);
                } else {
                    $res[] = $left[0];
                    $left = array_slice($left, 1);
                }
            } elseif ($sort_dir == self::SORT_DIR_DESC) {
                if ($left[0][$sort_key] < $right[0][$sort_key]) {
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
