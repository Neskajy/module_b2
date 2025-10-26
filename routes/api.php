<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ReviewsController;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

Route::prefix("auth")->group(function () {
    Route::post("register", [UserController::class, "register"]);
    Route::post("login", [UserController::class, "login"]);
    Route::post("logout", [UserController::class, "logout"])->middleware("auth:sanctum");
});

Route::get("locations", [LocationController::class, "index"]);
Route::middleware(\App\Http\Middleware\CheckIsAdmin::class)->group(function() {
    Route::post("locations", [LocationController::class, "store"])->middleware("auth:sanctum");
    Route::patch("locations/{id}", [LocationController::class, "update"])->middleware("auth:sanctum");
    Route::delete("locations/{id}", [LocationController::class, "destroy"])->middleware("auth:sanctum");
});

Route::get("feedbacks", [ReviewsController::class, "index"]);
Route::get("feedbacks/my", [ReviewsController::class, "my"])->middleware("auth:sanctum");
Route::post("feedbacks", [ReviewsController::class, "store"])->middleware("auth:sanctum");
Route::patch("feedbacks/{id}", [ReviewsController::class, "update"])->middleware("auth:sanctum");
Route::delete("feedbacks/{id}", [ReviewsController::class, "destroy"])->middleware("auth:sanctum");
Route::middleware(\App\Http\Middleware\CheckIsAdmin::class)->group(function() {
    Route::get("feedbacks/created", [ReviewsController::class, "created"]);
    Route::get("feedbacks/{id}/status", [ReviewsController::class, "changeStatus"]);
});
