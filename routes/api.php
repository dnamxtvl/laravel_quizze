<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GamerController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizzesController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\GamerMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['auth:sanctum', 'verified']);

Route::post('/admin/login', [AuthController::class, 'login'])->name('auth.admin.login');
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/email/verify-register/', [AuthController::class, 'verifyOTPAfterRegister'])->name('auth.verifyOTPAfterRegister');
    Route::post('/email/verify-login/', [AuthController::class, 'verifyOTPAfterLogin'])->name('auth.verifyOTPAfterLogin');
    Route::get('/email/resend-verify-email/{otpId}', [AuthController::class, 'resendVerifyEmail'])->middleware(['throttle:6,1'])->name('auth.resendVerificationNotification');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware(['throttle:6,1', 'guest'])->name('auth.forgotPassword');
    Route::post('/forgot-password/verify-otp', [AuthController::class, 'verifyOTPForgotPassword'])->middleware('guest')->name('auth.verifyOTPForgotPassword');
    Route::get('/forgot-password/resend-otp/{otpId}', [AuthController::class, 'resendOTPForgotPassword'])->middleware(['throttle:6,1'])->name('auth.resendOTPForgotPassword');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('guest')->name('auth.resetPassword');
    Route::get('/reset-password/{token}', function (string $token) {
        return view('auth.reset-password', ['token' => $token]);
    })->middleware('guest')->name('password.reset');
    Route::get('/google-sign-in-url', [AuthController::class, 'getGoogleSignInUrl'])->name('auth.loginGoogle');
    Route::get('/google-callback', [AuthController::class, 'loginCallback'])->name('auth.loginCallback');
});

Route::group(['middleware' => ['auth:api', 'verified']], function () {
    Route::prefix('admin')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.admin.logout');
        Route::prefix('quizzes')->group(function () {
            Route::middleware(['is_admin'])->group(function () {
                Route::get('/list', [QuizzesController::class, 'listQuizzesPagination'])->name('quizzes.list');
                Route::post('/accept-share/{token}', [QuizzesController::class, 'acceptShareQuiz'])->name('quizzes.accept-share');
                Route::get('/detail-share/{token}', [QuizzesController::class, 'detailShareQuiz'])->name('quizzes.detail-share');
                Route::post('/reject-share/{token}', [QuizzesController::class, 'rejectShareQuiz'])->name('quizzes.reject-share');
            });
            Route::post('/create', [QuizzesController::class, 'createQuiz'])->name('quizzes.create');
            Route::post('/delete/{quizId}', [QuizzesController::class, 'deleteQuiz'])->name('quizzes.delete');
            Route::get('/list-question/{quizId}', [QuizzesController::class, 'listQuestionOfQuiz'])->name('quizzes.list-question-of-quiz');
            Route::put('/update-question/{questionId}', [QuestionController::class, 'updateQuestion'])->name('questions.update');
            Route::post('/create-question/{quizId}', [QuestionController::class, 'createQuestion'])->name('questions.add');
            Route::post('/delete-question/{questionId}', [QuestionController::class, 'deleteQuestion'])->name('questions.delete');
            Route::post('/share/{quizId}', [QuizzesController::class, 'shareQuiz'])->name('quizzes.share');
            Route::get('/search', [QuizzesController::class, 'allQuizzesPagination'])->name('quizzes.all')->middleware('is_system');
        });
        Route::prefix('room')->group(function () {
            Route::middleware(['is_admin'])->group(function () {
                Route::post('/create/{quizId}', [RoomController::class, 'createRoom'])->name('rooms.create');
                Route::get('/check-valid/{quizId}', [RoomController::class, 'checkValidRoom'])->name('rooms.check-valid');
                Route::post('/start', [RoomController::class, 'startRoom'])->name('rooms.start');
                Route::post('/next-question', [RoomController::class, 'nextQuestion'])->name('rooms.next-question');
                Route::post('/end-game/{roomId}', [RoomController::class, 'adminEndGame'])->name('rooms.end-game');
            });
            Route::get('/list-report', [RoomController::class, 'getListRoomReport'])->name('rooms.list-report');
            Route::post('/delete-report/{roomId}', [RoomController::class, 'deleteReport'])->name('rooms.delete-report');
            Route::get('/detail/{roomId}', [RoomController::class, 'getDetailRoomReport'])->name('rooms.detail');
        });
        Route::prefix('notification')->group(function () {
            Route::get('/list', [NotificationController::class, 'listNotify'])->name('notifications.list');
            Route::post('/delete/{notifyId}', [NotificationController::class, 'deleteNotify'])->name('notifications.delete');
        });

        Route::middleware(['is_system'])->group(function () {
            Route::prefix('user')->group(function () {
                Route::get('/search', [UserController::class, 'search'])->name('user.search');
                Route::post('/update/{userId}', [UserController::class, 'update'])->name('user.update');
                Route::post('/disable/{userId}', [UserController::class, 'disable'])->name('user.disable');
                Route::post('/active/{userId}', [UserController::class, 'active'])->name('user.active');
                Route::post('/delete/{userId}', [UserController::class, 'delete'])->name('user.delete');
                Route::get('/search-elk/{keyword}', [UserController::class, 'searchByElk'])->name('user.search-by-elk');
            });
        });
        Route::get('/get-profile/{userId}', [UserController::class, 'detail'])->name('user.detail');
        Route::post('/change-password/{userId}', [UserController::class, 'changePassword'])->name('user.change-password');

        Route::prefix('category')->group(function () {
            Route::get('/list', [CategoryController::class, 'listCategory'])->name('categories.list');
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
