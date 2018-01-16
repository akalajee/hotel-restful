# Hotel RESTful 
[![Build Status](https://travis-ci.org/akalajee/hotel-restful.svg?branch=master)](https://travis-ci.org/akalajee/hotel-restful)
[![Maintainability](https://api.codeclimate.com/v1/badges/171c260f3c7c9ddae906/maintainability)](https://codeclimate.com/github/akalajee/hotel-restful/maintainability)

Hotel RESTful API is a lumen based project, to query remote API-server provider for hotel data, and make filtration and sorting for the hotels based on hotel name, price, city and availability.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine for development and testing purposes.

### Prerequisites

What things you need to install the software and how to install them

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

1. "/find" => will return all data as provided from remote 
2. "/find/{sort_key}/{sort_dir}" => will return all data sorted by key ASC or DESC based on sort_dir
3. "/find/search_hotel/{search_hotel}/{sort_key}/{sort_dir}" => will filter data based on hotel name sorted by key ASC or DESC based on sort_dir
4. "/find/search_city/{search_city}/{sort_key}/{sort_dir}" => will filter data based on city name sorted by key ASC or DESC based on sort_dir
5. "/find/search_price/{search_price}/{sort_key}/{sort_dir}" => will filter data based on price range sorted by key ASC or DESC based on sort_dir
6. "/find/search_date/{search_date}/{sort_key}/{sort_dir}" => will filter data based on availabilty date range sorted by key ASC or DESC based on sort_dir

##### Parameters Rule:
- search_price: '$'lower_bound_amount:'$'upper_bound_amount
- search_date: lower_bound_date(dd-mm-yyyy):upper_bound_date(dd-mm-yyyy)
- sort_key: 'hotel' and 'price'
- sort_dir: 1 for ascending and -1 for descending

## Running the tests

To run the tests, please run the following command from the project home

```
.\vendor\bin\phpunit --testdox OR .\vendor\bin\phpunit
```

## Built With

* [Lumen](https://lumen.laravel.com/) - The stunningly fast micro-framework by Laravel.

## Authors

* **Abdulghani Kalaji** - *Initial work* - [Hotel RESTful](https://github.com/akalajee/hotel-restful)
