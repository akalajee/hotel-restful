# Hotel RESTful 
[![Build Status](https://travis-ci.org/akalajee/hotel-restful.svg?branch=master)](https://travis-ci.org/akalajee/hotel-restful)
[![Maintainability](https://api.codeclimate.com/v1/badges/171c260f3c7c9ddae906/maintainability)](https://codeclimate.com/github/akalajee/hotel-restful/maintainability)

Hotel RESTful API is a lumen based project, to query remote API-server provider for hotel data, and make filtration and sorting for the hotels based on hotel name, price, city and availability.
Following points where considered while developing the project

- Fetching the data directly from the URL and not create a JSON file
- Using PHP >= 7.1 for the development
- Writing Six Unit tests in PHPUnit, to test all possible routes for the application
- Implementing travis-CI, and adding the build status badges to the project README file
- Using codeclimate to estimate the code quality, and adding it's badge to the project README file
- Not using any database or full text search engines
- Used merge sort as a sorting algorithm as it performs O(n log n) in both worst and best case scenarios


## Getting Started

### Prerequisites

```
PHP >= 7.1
Composer
OpenSSL PHP Extension
PDO PHP Extension
Mbstring PHP Extension
```

### Installing

Clone the project, and execute the following command from the project home

```
php composer install
```

### Running the Project

Either run the php project thorugh wamp/lamp stack, or use php build-in server with the following command

```
php -S localhost:8000 -t public
```

After launching the application, the following GET routes are available to query the data:

1. "/v1/hotels" => will return all data as provided from remote 
2. "/v1/hotels/{sortKey}/{sortDir}" => will return all data sorted by key ASC or DESC based on sortDir
3. "/v1/hotels/searchHotel/{searchHotel}/{sortKey}/{sortDir}" => will filter data based on hotel name sorted by key ASC or DESC based on sortDir
4. "/v1/hotels/searchCity/{searchCity}/{sortKey}/{sortDir}" => will filter data based on city name sorted by key ASC or DESC based on sortDir
5. "/v1/hotels/searchPrice/{searchPrice}/{sortKey}/{sortDir}" => will filter data based on price range sorted by key ASC or DESC based on sortDir
6. "/v1/hotels/searchDate/{searchDate}/{sortKey}/{sortDir}" => will filter data based on availabilty date range sorted by key ASC or DESC based on sortDir

##### Parameters Rule
- searchHotel: string
- searchCity: string
- searchPrice: '$'+((number) lower_bound_amount):'$'+((number) upper_bound_amount)
- searchDate: lower_bound_date(dd-mm-yyyy):upper_bound_date(dd-mm-yyyy)
- sortKey: 'hotel' and 'price'
- sortDir: 1 for ascending and -1 for descending

##### Examples
 - 'https://project_url/v1/hotels',
 - 'https://project_url/v1/hotels/hotel/1',
 - 'https://project_url/v1/hotels/searchHotel/Con/hotel/1',
 - 'https://project_url/v1/hotels/searchPrice/$80:$100/price/-1',
 - 'https://project_url/v1/hotels/searchDate/10-10-2020:11-10-2020/hotel/-1',
 - 'https://project_url/v1/hotels/searchCity/Dubai/hotel/1'

## Running the tests

To run the tests, please run one of the following commands from the project home

```
.\vendor\bin\phpunit --testdox
.\vendor\bin\phpunit
```

## Built With

* [Lumen](https://lumen.laravel.com/) - The stunningly fast micro-framework by Laravel.

## Authors

* **Abdulghani Kalaji** - *Initial work* - [Hotel RESTful](https://github.com/akalajee/hotel-restful)
