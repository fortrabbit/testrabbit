<?php

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

Route::get('/', [Controller::class, 'index']);

Route::get('/tests/{test}', [Controller::class, 'test']);
