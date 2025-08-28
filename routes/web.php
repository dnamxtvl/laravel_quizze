<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Stevebauman\Location\Facades\Location;

Route::get('/test/', function () {
    //dd(Location::get('116.96.46.236'));
    Log::error('test error channel');
    Log::info('test info channel');
    Log::debug('test debug channel');
    Log::warning('test warning channel');
    dd(\App\Models\Category::all()->pluck('name')->toArray());
});
