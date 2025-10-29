<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ReviewsController;
use App\Http\Middleware\CheckUser;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

Route::prefix("auth")->group(function () {
    Route::post("register", [UserController::class, "register"]);
    Route::post("login", [UserController::class, "login"]);
    Route::post("logout", [UserController::class, "logout"])->middleware(CheckUser::class);
});


Route::get("user/me", [UserController::class, "user"])->middleware(CheckUser::class);

Route::get("locations", [LocationController::class, "index"]);
Route::middleware(\App\Http\Middleware\CheckIsAdmin::class)->group(function() {
    Route::post("locations", [LocationController::class, "store"])->middleware(CheckUser::class);
    Route::patch("locations/{id}", [LocationController::class, "update"])->middleware(CheckUser::class);
    Route::delete("locations/{id}", [LocationController::class, "destroy"])->middleware(CheckUser::class);
});

Route::middleware(\App\Http\Middleware\CheckIsAdmin::class)->group(function() {
    Route::get("feedbacks/created", [ReviewsController::class, "created"]);
    Route::patch("feedbacks/{id}/status", [ReviewsController::class, "status"]);
});
Route::get("feedbacks", [ReviewsController::class, "index"]);
Route::get("feedbacks/my", [ReviewsController::class, "my"])->middleware(CheckUser::class);
Route::get("feedbacks/{id}", [ReviewsController::class, "getFeedback"])->middleware(CheckUser::class);
Route::post("feedbacks", [ReviewsController::class, "store"])->middleware(CheckUser::class);
Route::patch("feedbacks/{id}", [ReviewsController::class, "update"])->middleware(CheckUser::class);
Route::delete("feedbacks/{id}", [ReviewsController::class, "destroy"])->middleware(CheckUser::class);
