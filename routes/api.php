<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ZoneController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/hello', [ApiController::class, 'hello']);

Route::get('/events/{eventId}', [EventController::class, 'listAnEvent']);
Route::post('/events/{eventId}/register', [EventController::class, 'registerForAnEvent']);

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/forgot-password', [PasswordController::class, 'forgotPassword']);
    Route::post('/reset-password', [PasswordController::class, 'resetPassword']);
    Route::post('/resend-verification-otp', [PasswordController::class, 'resendVerificationOtp']);
    Route::post('/verify-email', [PasswordController::class, 'verifyEmail']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/update-password', [PasswordController::class, 'updatePassword']);
});

Route::middleware(['auth:sanctum'/* , 'is_admin' */])->prefix('admin')->group(function () {

    Route::prefix('events')->group(function () {
        Route::get('/summary', [EventController::class, 'eventsSummary']);
        Route::get('/', [EventController::class, 'listEvents']);
        Route::post('/', [EventController::class, 'createEvent']);
        Route::patch('/{eventId}', [EventController::class, 'updateEvent']);

        Route::prefix('{eventId}/participants')->group(function () {
            Route::get('/', [EventController::class, 'eventParticipants']);
            Route::get('/charts', [EventController::class, 'eventParticipantCounts']);
            Route::post('/{eventParticipantId}', [EventController::class, 'updateEventParticipantAttendance']);
        });
    });

    Route::prefix('zones')->group(function () {
        Route::get('/', [ZoneController::class, 'getAllZones']);
        Route::post('/', [ZoneController::class, 'createAZone']);
        Route::patch('/{zoneId}', [ZoneController::class, 'updateAZone']);
    });

});
// admin group

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
