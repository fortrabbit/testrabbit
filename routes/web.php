<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\LogVolumeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [Controller::class, 'index']);

Route::get('/imagick-perf', [Controller::class, 'perf']);
Route::get('/imagick-perf/run', [Controller::class, 'perfRun']);

Route::get('/php-errors', [Controller::class, 'phpErrors']);
Route::get('/php-errors/emit', [Controller::class, 'emit']);

Route::get('/log-volume', [LogVolumeController::class, 'index']);
Route::get('/log-volume/status', [LogVolumeController::class, 'status']);
Route::post('/log-volume/start', [LogVolumeController::class, 'start']);
Route::post('/log-volume/{run}/stop', [LogVolumeController::class, 'stop']);

Route::get('/tests/{test}', [Controller::class, 'test']);
