<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:api']], function () {
    Route::post('/check-login', function (Request $request) {
        return $request->user();
    });
});

Route::post('/admin/login', [AuthController::class, 'login'])->name(name: 'auth.admin.login');
Route::group(['middleware' => ['auth:api', 'verified']], function () {
    Route::prefix('admin')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name(name: 'auth.admin.logout');
    });
});
