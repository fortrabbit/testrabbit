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

use App\Jobs\MongoTestJob;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/mongodb', function () {
    echo "Try mongodb connection from HTTP";
    MongoTestJob::dispatchNow();
});

Route::get('/cache', function () {
    echo "Driver:" . config('cache.default') . "<br>";
    if(!cache("somekey")) {
        cache(['somekey' => time()], 10);
    }
    echo "Result:" . cache("somekey");
});



Route::get('/apcu', function () {
    $faker = Faker\Factory::create();

    apcu_clear_cache();
    echo 'Cleared APCu cache.<br>';

    $keys = [];
    for($i = 0; $i < 1000; $i++) {
        $keys[] = $key = $faker->unique()->sha1;
        apcu_store($key, $faker->name);
    }
    echo 'Stored 1000 items in APCu.<br>';

    for($i = 0; $i < 500; $i++) {
        $key_id = $faker->numberBetween(0,999);
        apcu_fetch($keys[$key_id]);
    }
    echo 'Fetched 500 random items from APCu.<br>';

    for($i = 0; $i < 100; $i++) {
        apcu_fetch($faker->sha1);
    }
    echo 'Fetched 100 missing items from APCu.<br>';

    dump(apcu_cache_info(), apcu_sma_info());
});
