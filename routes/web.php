<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Stevebauman\Location\Facades\Location;

Route::get('/healthcheck', function () {
    return response()->json(['status' => 'OK'], 200);
});

Route::get('/test/', function () {
    Log::error('test error channel');
    dd(\App\Models\Category::all()->pluck('name')->toArray());
});
