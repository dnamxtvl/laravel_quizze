<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\GamerController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizzesController;
use App\Http\Controllers\RoomController;
use App\Http\Middleware\GamerMiddleware;
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
            Route::post('/create', [QuizzesController::class, 'createQuiz'])->name('quizzes.create');
            Route::post('/delete/{quizId}', [QuizzesController::class, 'deleteQuiz'])->name('quizzes.delete');
            Route::get('/list-question/{quizId}', [QuizzesController::class, 'listQuestionOfQuiz'])->name('quizzes.list-question-of-quiz');
            Route::put('/update-question/{questionId}', [QuestionController::class, 'updateQuestion'])->name('questions.update');
            Route::post('/create-question/{quizId}', [QuestionController::class, 'createQuestion'])->name('questions.add');
            Route::post('/delete-question/{questionId}', [QuestionController::class, 'deleteQuestion'])->name('questions.delete');
        });
        Route::prefix('room')->group(function () {
            Route::post('/create/{quizId}', [RoomController::class, 'createRoom'])->name('rooms.create');
            Route::get('/check-valid/{quizId}', [RoomController::class, 'checkValidRoom'])->name('rooms.check-valid');
            Route::get('/detail/{roomId}', [RoomController::class, 'getDetailRoomReport'])->name('rooms.detail');
            Route::post('/start', [RoomController::class, 'startRoom'])->name('rooms.start');
            Route::post('/next-question', [RoomController::class, 'nextQuestion'])->name('rooms.next-question');
            Route::post('/end-game/{roomId}', [RoomController::class, 'adminEndGame'])->name('rooms.end-game');
            Route::get('/list-report', [RoomController::class, 'getListRoomReport'])->name('rooms.list-report');
            Route::post('/delete-report/{roomId}', [RoomController::class, 'deleteReport'])->name('rooms.delete-report');
        });
    });
});

Route::prefix('user')->group(function () {
    Route::prefix('room')->group(function () {
        Route::post('/verify-code', [RoomController::class, 'validateRoomCode'])->name('rooms.validate-code');
        Route::get('/list-question/{roomToken}', [RoomController::class, 'listQuestionOfRoom'])->name('rooms.list-question');
    });
    Route::prefix('gamer')->group(function () {
        Route::post('/create-setting', [GamerController::class, 'createGameSetting'])->name('gamer.create-setting');
        Route::post('/submit-answer', [GamerController::class, 'submitAnswer'])->name('gamer.submit-answer');
        Route::post('/out-game/{token}', [GamerController::class, 'userOutGame'])->name('gamer.out-game');
        Route::post('/submit-homework/{token}', [GamerController::class, 'submitHomework'])->name('gamer.submit-homework');
    });
})->middleware(GamerMiddleware::class);
