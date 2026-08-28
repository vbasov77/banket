<?php

use App\API\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\API\Controllers\MessageController;
use App\API\Controllers\ChatController;
use App\API\Controllers\Auth\AuthTokenController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/store_msg', [MessageController::class, 'store']);
    Route::get('/delete_message', [MessageController::class, 'deleteMsgApi'])->name('delete.message.api');

    Route::get('/chats/has-new', [ChatController::class, 'hasNewMessages']);
    Route::get('/chats', [ChatController::class, 'index']);



    Route::post('/auth/save-fcm-token', [AuthTokenController::class, 'saveFcmToken']);


});