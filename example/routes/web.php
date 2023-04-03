<?php

use App\Http\Controllers\HomeController;
use App\Http\Middleware\FlagshipMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [HomeController::class, 'index'])
    ->name("home")
    ->middleware(FlagshipMiddleware::class); //Assigning FlagshipMiddleware to route home

Route::get("/about", function () {
    return view("about");
});
