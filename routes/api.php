<?php

use App\Http\Controllers\Api\Auth\UserController;
use App\Http\Controllers\Api\Auth\AdminController;
use App\Http\Controllers\Api\CategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['prefix' => 'v1'], function () {

    // User API endpoint
    Route::group(['prefix' => 'user'], function () {
        Route::post('create', [UserController::class, 'store'])->name('user.create');
        Route::post('login', [UserController::class, 'login'])->name('user.login');

        Route::group(['middleware' => 'jwt'], function () {
            Route::get('/', [UserController::class, 'profile'])->name('user');
        });
    });

    // Admin API endpoint
    Route::group(['prefix' => 'admin'], function () {
        Route::post('create', [AdminController::class, 'store'])->name('admin.create');
        Route::post('login', [AdminController::class, 'login'])->name('admin.login');
        Route::post('logout', [AdminController::class, 'logout'])->name('admin.logout');

        Route::group(['middleware' => 'jwt'], function () {
            Route::get('user-listing', [AdminController::class, 'allUsers'])->name('admin.user-listing');
            Route::put('user-edit/{uuid}', [AdminController::class, 'editUser'])->name('admin.user-edit');
            Route::delete('user-delete/{uuid}', [AdminController::class, 'deleteUser'])->name('admin.user-delete');
        });
    });

    // Category API endpoint
    Route::group(['prefix' => 'category'], function () {
        Route::get('categories', [CategoryController::class, 'all'])->name('categories');
        Route::get('{uuid}', [CategoryController::class, 'fetch'])->name('category.fetch');
        Route::group(['middleware' => 'jwt'], function () {
            Route::post('create', [CategoryController::class, 'store'])->name('category.create');
            Route::put('{uuid}', [CategoryController::class, 'edit'])->name('category.edit');
            Route::delete('{uuid}', [CategoryController::class, 'delete'])->name('category.delete');
        });
    });



});

// EOF
