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
use App\Http\Controllers\AdminController;
use App\Http\Controllers\MasyarakatController;
use App\Http\Controllers\InstitusiController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    $user = $request->user();
    $user->getRoles();

    return response()->json($user);
});

route::get('/users', function () {
    return UserResource::collection(User::all());
});

Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth:sanctum');
Route::post('/reset-password', [AdminController::class, 'ubahPassword']);
Route::get('/notification', [MasyarakatController::class, 'notification'])->middleware('auth:sanctum');

Route::prefix('report')->group(function () {
    // get all
    Route::get('/', [ReportController::class, 'index']);

    // get all reports with 'null', 'Menunggu', 'Ditolak'
    Route::get('/admin', [ReportController::class, 'indexAdmin']);

    // post
    Route::post('/', [ReportController::class, 'store'])->middleware('auth:sanctum');

    // search
    Route::get('/search', [ReportController::class, 'searchReports']);

    // like
    Route::post('/like', [ReportController::class, 'like'])->middleware('auth:sanctum');

    // bookmark
    Route::post('/bookmark', [ReportController::class, 'bookmark'])->middleware('auth:sanctum');

    // post diskusi
    Route::post('/diskusi/{id}', [ReportController::class, 'diskusiStore'])->middleware('auth:sanctum');

    // get diskusi
    Route::get('/diskusi/{id}', [ReportController::class, 'diskusiShow'])->middleware('auth:sanctum');

    // get by category
    Route::get('/category/{categoryId}', [ReportController::class, 'getByCategory']);

    // get by status
    Route::get('/status/{status}', [ReportController::class, 'getReportsByStatus']);

    // update status
    Route::post('/status/{id}', [ReportController::class, 'addStatus']);

    // get liked reports
    Route::get('/liked', [ReportController::class, 'likedReports'])->middleware('auth:sanctum');

    // Get by id
    Route::get('/{id}', [ReportController::class, 'show']);

    // update
    Route::post('/{id}', [ReportController::class, 'update'])->middleware('auth:sanctum');

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

    //search
    Route::get('/search', [BeritaController::class, 'searchBerita']);

    // update
    Route::post('/{id}', [BeritaController::class, 'update'])->middleware('auth:sanctum');

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

Route::prefix('pemerintah')->middleware('auth:sanctum')->group(function () {

    // get all pemerintah users
    Route::get('/', [AdminController::class, 'indexPemerintah']);

    // store pemerintah user
    Route::post('/', [AdminController::class, 'storePemerintah']);

    //search
    Route::get('/search', [AdminController::class, 'searchPemerintah']);

    // show pemerintah by id
    Route::post('/{id}', [AdminController::class, 'updatePemerintah']);

    // show pemerintah by id
    Route::get('/{id}', [AdminController::class, 'showPemerintah']);

    // delete pemerintah by id
    Route::delete('/{id}', [AdminController::class, 'destroyPemerintah']);
});

Route::prefix('masyarakat')->group(function () {
    Route::get('/search', [AdminController::class, 'searchMasyarakatByPhone']);
});

Route::apiResource('institusi', InstitusiController::class);
