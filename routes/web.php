<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/apcu', function () {
  $faker = Faker\Factory::create();

  apcu_clear_cache();

  for($i = 0; $i < 1000; $i++) {
    apcu_store($faker->unique()->sha1, $faker->name);
  }

  echo 'Stored 1000 items in APCu.';

  dump(apcu_cache_info(), apcu_sma_info());
});
