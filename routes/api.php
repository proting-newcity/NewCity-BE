<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\UserResource;

use App\Models\User;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\BeritaController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\UserController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    $user = $request->user(); 
    $user->getRoles(); 
    
    return response()->json($user);
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
    Route::post('/', [ReportController::class, 'store'])->middleware('auth:sanctum');
    
    // search
    Route::get('/search', [ReportController::class, 'searchReports']);
    
    // like
    Route::post('/like', [ReportController::class, 'like'])->middleware('auth:sanctum');

    // bookmark
    Route::post('/bookmark', [ReportController::class, 'bookmark'])->middleware('auth:sanctum');
    
    // get by category
    Route::get('/category/{categoryId}', [ReportController::class, 'getByCategory']);
    
    // Get by id
    Route::get('/{id}', [ReportController::class, 'show']);
    
    // update
    Route::put('/{id}', [ReportController::class, 'update'])->middleware('auth:sanctum');
    
    // delete
    Route::delete('/{id}', [ReportController::class, 'destroy'])->middleware('auth:sanctum');

});

Route::prefix('berita')->group(function () {
    // get all berita
    Route::get('/', [BeritaController::class, 'indexWeb']);
    
    // post berita
    Route::post('/', [BeritaController::class, 'store'])->middleware('auth:sanctum');

    // like
    Route::post('/like', [BeritaController::class, 'like'])->middleware('auth:sanctum');
    
    // get by category berita
    Route::get('/category/{categoryId}', [BeritaController::class, 'getByCategory']);
    
    // update
    Route::put('/{id}', [BeritaController::class, 'update'])->middleware('auth:sanctum');
    
    // delete
    Route::delete('/{id}', [BeritaController::class, 'destroy'])->middleware('auth:sanctum');
});

Route::prefix('kategori')->group(function () {
    // get all kategori report
    Route::get('/report', [KategoriController::class, 'indexReport']);

    // get all kategori report
    Route::get('/berita', [KategoriController::class, 'indexBerita']);
    
    // post kategori report
    Route::post('/report', [KategoriController::class, 'storeReport'])->middleware('auth:sanctum');

    // post kategori berita
    Route::post('/berita', [KategoriController::class, 'storeBerita'])->middleware('auth:sanctum');
    
});

// get all pemerintah users
Route::get('/pemerintah', [UserController::class, 'index'])->middleware('auth:sanctum');