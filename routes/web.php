<?php

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

Route::get('/', [Controller::class, 'index']);

Route::get('/imagick-perf', [Controller::class, 'perf']);
Route::get('/imagick-perf/run', [Controller::class, 'perfRun']);

Route::get('/tests/{test}', [Controller::class, 'test']);
