<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\QuizzesController;
use App\Http\Controllers\RoomController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum', 'verified']);

Route::post('/admin/login', [AuthController::class, 'login'])->name('auth.admin.login');
Route::group(['middleware' => ['auth:api', 'verified']], function () {
    Route::prefix('admin')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.admin.logout');
        Route::prefix('quizzes')->group(function () {
            Route::get('/list', [QuizzesController::class, 'listQuizzesPagination'])->name('quizzes.list');
        });
        Route::prefix('room')->group(function () {
            Route::post('/create/{quizId}', [RoomController::class, 'createRoom'])->name('rooms.create');
            Route::get('/check-valid/{quizId}', [RoomController::class, 'checkValidRoom'])->name('rooms.check-valid');
        });
    });
});
