<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\UserResource;

use App\Models\User;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ReportController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

route::get('/users',function(){
    return UserResource::collection(User::all());
});

Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth:sanctum');

Route::prefix('report')->group(function () {
    // get all
    Route::get('/', [ReportController::class, 'index']);
    
    // post
    Route::post('/', [ReportController::class, 'store']);
    
    // get by category
    Route::get('/category/{categoryId}', [ReportController::class, 'getByCategory']);
    
    // Get by id
    Route::get('/{id}', [ReportController::class, 'show']);
    
    // update
    Route::put('/{id}', [ReportController::class, 'update']);
    
    // delete
    Route::delete('/{id}', [ReportController::class, 'destroy']);
});