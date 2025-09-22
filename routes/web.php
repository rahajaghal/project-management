<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\SocialiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
//Route::get('auth/google',[SocialiteController::class,'redirectToGoogle']);
//Route::get('auth/google/callback',[SocialiteController::class,'handleGoogleCallback']);
////
//Route::get('auth/github',[SocialiteController::class,'redirectToGithub']);
//Route::get('auth/github/callback',[SocialiteController::class,'handleGithubCallback']);

Route::get('/auth/{provider}', [AuthController::class,'redirectToProvider']);
Route::get('/auth/{provider}/callback', [AuthController::class,'handleProviderCallback']);
