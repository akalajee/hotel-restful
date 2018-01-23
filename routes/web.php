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


$router->group(['prefix' => 'v1'], function () use ($router) {
    $router->get('/hotels', [
        'as' => 'byNone',
        'uses' => 'SearchController@find'
    ]);
    $router->get('/hotels/{sortKey}/{sortDir}', [
        'as' => 'byNoneSort',
        'uses' => 'SearchController@find'
    ]);
    $router->get('/hotels/searchHotel/{searchHotel}/{sortKey}/{sortDir}', [
        'as' => 'byHotel',
        'uses' => 'SearchController@find'
    ]);
    $router->get('/hotels/searchCity/{searchCity}/{sortKey}/{sortDir}', [
        'as' => 'byCity',
        'uses' => 'SearchController@find'
    ]);
    $router->get('/hotels/searchPrice/{searchPrice}/{sortKey}/{sortDir}', [
        'as' => 'byPrice',
        'uses' => 'SearchController@find'
    ]);
    $router->get('/hotels/searchDate/{searchDate}/{sortKey}/{sortDir}', [
        'as' => 'byDate',
        'uses' => 'SearchController@find'
    ]);
});
