<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Stevebauman\Location\Facades\Location;

Route::get('/test/', function () {
    Log::error('test error channel');
    Log::info('test info channel');
    Log::debug('test debug channel');
    Log::warning('test warning channel');
    dd(\App\Models\Category::all()->pluck('name')->toArray());
});
