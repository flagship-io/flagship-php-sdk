<?php

/** @var \Laravel\Lumen\Routing\Router $router */

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

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'env'], function () use ($router) {
    $router->get('/', [
        'middleware' => 'flagship',
        'uses' => 'EnvController@index'
    ]);
    $router->put('/', 'EnvController@update');
});

$router->group(['prefix' => 'visitor', 'middleware' => 'flagship',], function () use ($router) {
    $router->get('/', 'VisitorController@index');
    $router->put('/', 'VisitorController@update');
});

$router->group(['prefix' => 'flag', 'middleware' => ['flagship','flagshipVisitor']], function () use ($router) {
    $router->get('/{key}/activate', 'FlagController@activeModification');
    $router->get('/{key}/info', 'FlagController@getModificationInfo');
    $router->get('/{key}', 'FlagController@getModification');
});
