<?php

use App\Http\Controllers\Api\ApiCheckerController;
use App\Http\Controllers\Api\ProductController;
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

Route::get('/', [ApiCheckerController::class, 'getStatusApi'])->name('apiCheck.getStatusApi');

Route::group(['prefix' => 'products'], function () {
    Route::get('/', [ProductController::class, 'index'])->name('products.getAllProducts');
    Route::post('/', [ProductController::class, 'store'])->name('products.createNewProduct');
    Route::get('/{code}', [ProductController::class, 'show'])->name('products.findProduct');
    Route::put('/{code}', [ProductController::class, 'update'])->name('products.updateProduct');
    Route::delete('/{code}', [ProductController::class, 'delete'])->name('products.deleteProduct');
});


