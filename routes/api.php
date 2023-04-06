<?php

use App\Http\Controllers\Api\Auth\UserController;
use App\Http\Controllers\Api\Auth\AdminController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\OrderStatusController;
use App\Http\Controllers\Api\FileController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PaymentController;
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

    // Brands API endpoint
    Route::group(['prefix' => 'brand'], function () {
        Route::get('brands', [BrandController::class, 'all'])->name('brands');
        Route::get('{uuid}', [BrandController::class, 'fetch'])->name('brand.fetch');
        Route::group(['middleware' => 'jwt'], function () {
            Route::post('create', [BrandController::class, 'store'])->name('brand.create');
            Route::put('{uuid}', [BrandController::class, 'edit'])->name('brand.edit');
            Route::delete('{uuid}', [BrandController::class, 'delete'])->name('brand.delete');
        });
    });

    // Order Statuses API endpoint
    Route::group(['prefix' => 'order-status'], function () {
        Route::get('order-statuses', [OrderStatusController::class, 'all'])->name('order-statuses');
        Route::get('{uuid}', [OrderStatusController::class, 'fetch'])->name('order-status.fetch');
        Route::group(['middleware' => 'jwt'], function () {
            Route::post('create', [OrderStatusController::class, 'store'])->name('order-status.create');
            Route::put('{uuid}', [OrderStatusController::class, 'edit'])->name('order-status.edit');
            Route::delete('{uuid}', [OrderStatusController::class, 'delete'])->name('order-status.delete');
        });
    });

    // File API endpoint
    Route::group(['prefix' => 'file'], function () {
        Route::get('{uuid}', [FileController::class, 'download'])->name('file.fetch');
        Route::group(['middleware' => 'jwt'], function () {
            Route::post('upload', [FileController::class, 'store'])->name('file.create');
        });
    });


    // Products API endpoint
    Route::group(['prefix' => 'product'], function () {
        Route::get('products', [ProductController::class, 'all'])->name('products');
        Route::get('{uuid}', [ProductController::class, 'fetch'])->name('product.fetch');
        Route::group(['middleware' => 'jwt'], function () {
            Route::post('create', [ProductController::class, 'store'])->name('product.create');
            Route::put('{uuid}', [ProductController::class, 'edit'])->name('product.edit');
            Route::delete('{uuid}', [ProductController::class, 'delete'])->name('product.delete');
        });
    });

    // Payments API endpoint
    Route::group(['prefix' => 'payment'], function () {
        Route::get('payments', [PaymentController::class, 'all'])->name('payments');
        Route::get('{uuid}', [PaymentController::class, 'fetch'])->name('payment.fetch');
        Route::group(['middleware' => 'jwt'], function () {
            Route::post('create', [PaymentController::class, 'store'])->name('payment.create');
            Route::put('{uuid}', [PaymentController::class, 'edit'])->name('payment.edit');
            Route::delete('{uuid}', [PaymentController::class, 'delete'])->name('payment.delete');
        });
    });



});

// EOF
