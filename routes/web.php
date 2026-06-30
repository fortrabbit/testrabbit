<?php

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

Route::get('/', [Controller::class, 'index']);

Route::get('/imagick-perf', [Controller::class, 'perf']);
Route::get('/imagick-perf/run', [Controller::class, 'perfRun']);

Route::get('/php-errors', [Controller::class, 'phpErrors']);
Route::get('/php-errors/emit', [Controller::class, 'emit']);

Route::get('/tests/{test}', [Controller::class, 'test']);
