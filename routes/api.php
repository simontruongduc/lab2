<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

    Route::prefix('registration')->group(function () {
        Route::post('login', [AuthController::class,'login']);
        Route::get('logout',[AuthController::class,'logout'])->middleware('checkLogin');
        Route::post('signup',[AuthController::class,'signup']);
        Route::get('verifyAccount/{email}/{token}',[AuthController::class,'verifyAccount']);
        Route::post('forgotPassword',[AuthController::class,'forgotPassword']);
        Route::get('checkVerifyToken/{token}',[AuthController::class,'checkVerifyToken']);
        Route::post('resetPassword',[AuthController::class,'resetPassword']);
        Route::post('changePassword',[AuthController::class,'changePassword'])->middleware('checkLogin');
        Route::post('profile',[AuthController::class,'updateProfile'])->middleware('checkLogin');
        Route::get('profile',[AuthController::class,'profile'])->middleware('checkLogin');
    });
