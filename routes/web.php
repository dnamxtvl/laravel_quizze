<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Stevebauman\Location\Facades\Location;
use Illuminate\Support\Facades\Mail;

Route::get('/healthcheck', function () {
    return response()->json(['status' => 'OK'], 200);
});

Route::get('/test/', function () {
    Log::error('test error channel');
    dd(\App\Models\Category::all()->pluck('name')->toArray());
});

Route::get('/test-mail/', function () {
    $email = 'hoangthanh22082000@gmail.com';
    Mail::raw('This is a test email from Laravel SES', function ($message) use ($email) {
        $message->to($email)
            ->subject('Test Email from Laravel');
    });
    Log::error('test error channel');
    dd(\App\Models\Category::all()->pluck('name')->toArray());
});
