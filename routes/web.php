<?php

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It is a breeze. Simply tell Lumen the URIs it should respond to
  | and give it the Closure to call when that URI is requested.
  |
 */


$router->group([], function () use ($router) {
    $router->get('/find', [
        'as' => 'byNone',
        'uses' => 'SearchController@find'
    ]);
    $router->get('/find/{sort_key}/{sort_dir}', [
        'as' => 'byNoneSort',
        'uses' => 'SearchController@find'
    ]);
    $router->get('/find/search_hotel/{search_hotel}/{sort_key}/{sort_dir}', [
        'as' => 'byHotel',
        'uses' => 'SearchController@find'
    ]);
    $router->get('/find/search_city/{search_city}/{sort_key}/{sort_dir}', [
        'as' => 'byCity',
        'uses' => 'SearchController@find'
    ]);
    $router->get('/find/search_price/{search_price}/{sort_key}/{sort_dir}', [
        'as' => 'byPrice',
        'uses' => 'SearchController@find'
    ]);
    $router->get('/find/search_date/{search_date}/{sort_key}/{sort_dir}', [
        'as' => 'byDate',
        'uses' => 'SearchController@find'
    ]);
});
