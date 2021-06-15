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
    return view('index');
});

$router->group(['prefix' => 'env'], function () use ($router) {
    $router->get('/', [
        'uses' => 'EnvController@index'
    ]);
    $router->put('/', 'EnvController@update');
});

$router->group(
    ['prefix' => 'visitor'],
    function () use ($router) {
        $router->get('/', 'VisitorController@index');

        $router->put('/', ['middleware' => 'CheckFlagshipSession|startFlagship', 'uses' => 'VisitorController@update']);

        $router->put(
            '/context/{key}',
            [
                'middleware' => 'CheckFlagshipSession|startFlagship|flagshipVisitor',
                'uses' => 'VisitorController@updateContext'
            ]
        );

        $router->put(
            '/consent',
            [
                'middleware' => 'CheckFlagshipSession|flagshipVisitor',
                'uses' => 'VisitorController@updateConsent'
            ]
        );
    }
);

$router->group(
    ['prefix' => 'authenticate','middleware' => 'CheckFlagshipSession|flagshipVisitor'],
    function () use ($router) {
        $router->put('/', 'AuthController@authenticate');
    }
) ;

$router->group(
    ['prefix' => 'unauthenticate','middleware' => 'CheckFlagshipSession|flagshipVisitor'],
    function () use ($router) {
        $router->put('/', 'AuthController@unauthenticate');
    }
) ;

$router->group(
    ['prefix' => 'flag', 'middleware' => ['CheckFlagshipSession','startFlagship','flagshipVisitor']],
    function () use ($router) {
        $router->get('/{key}/activate', 'FlagController@activeModification');
        $router->get('/{key}/info', 'FlagController@getModificationInfo');
        $router->get('/{key}', 'FlagController@getModification');
    }
);

$router->group(
    ['prefix' => 'hit', 'middleware' => ['CheckFlagshipSession','startFlagship', 'flagshipVisitor']],
    function () use ($router) {
        $router->post('/', 'HitController@sendHit');
    }
);

$router->group(['prefix' => 'logs'], function () use ($router) {
    $router->get('/', 'LogController@index');
});
$router->get('/clear', 'LogController@clear');
