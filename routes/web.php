<?php

use Illuminate\Support\Facades\Route;
use Stevebauman\Location\Facades\Location;

Route::get('/test/', function () {
    //dd(Location::get('116.96.46.236'));
    dd(\App\Models\Category::all()->pluck('name')->toArray());
});
