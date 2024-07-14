<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ComponentController;
use App\Http\Controllers\ComponentProductController;
use App\Http\Controllers\ComponentTypeController;
use App\Http\Controllers\ComponentWithProductsController;
use App\Http\Controllers\DishComponentController;
use App\Http\Controllers\DishController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\MenuItemOrderController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseItemController;
use App\Http\Controllers\PurchaseOptionController;
use App\Http\Controllers\UnitController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/components/all', [ComponentController::class, 'index']);
Route::get('/components/{id}', [ComponentController::class, 'show']);
Route::put('/components/update/{id}', [ComponentController::class, 'update']);
Route::post('/components/create', [ComponentController::class, 'store']);
Route::delete('/components/delete/{id}', [ComponentController::class, 'destroy']);

Route::get('/components-with-products/all', [ComponentWithProductsController::class, 'index']);
Route::get('/components-with-products/{id}', [ComponentWithProductsController::class, 'show']);
Route::put('/components-with-products/update/{id}', [ComponentWithProductsController::class, 'update']);
Route::post('/components-with-products/create', [ComponentWithProductsController::class, 'store']);

Route::get('/component-products/all', [ComponentProductController::class, 'index']);
Route::get('/component-products/{id}', [ComponentProductController::class, 'show']);
Route::put('/component-products/update/{id}', [ComponentProductController::class, 'update']);
Route::post('/component-products/create', [ComponentProductController::class, 'store']);
Route::delete('/component-products/delete/{id}', [ComponentProductController::class, 'destroy']);

Route::get('/component-types/all', [ComponentTypeController::class, 'index']);
Route::get('/component-types/{id}', [ComponentTypeController::class, 'show']);
Route::put('/component-types/update/{id}', [ComponentTypeController::class, 'update']);
Route::post('/component-types/create', [ComponentTypeController::class, 'store']);
Route::delete('/component-types/delete/{id}', [ComponentTypeController::class, 'destroy']);

Route::get('/dish-components/all', [DishComponentController::class, 'index']);
Route::get('/dish-components/{id}', [DishComponentController::class, 'show']);
Route::put('/dish-components/update/{id}', [DishComponentController::class, 'update']);
Route::post('/dish-components/create', [DishComponentController::class, 'store']);
Route::delete('/dish-components/delete/{id}', [DishComponentController::class, 'destroy']);

Route::get('/dishes/all', [DishController::class, 'index']);
Route::get('/dishes/{id}', [DishController::class, 'show']);
Route::put('/dishes/update/{id}', [DishController::class, 'update']);
Route::post('/dishes/create', [DishController::class, 'store']);
Route::delete('/dishes/delete/{id}', [DishController::class, 'destroy']);

Route::get('/distributors/all', [DistributorController::class, 'index']);
Route::get('/distributors/{id}', [DistributorController::class, 'show']);
Route::put('/distributors/update/{id}', [DistributorController::class, 'update']);
Route::post('/distributors/create', [DistributorController::class, 'store']);
Route::delete('/distributors/delete/{id}', [DistributorController::class, 'destroy']);

Route::get('/menu-items/all', [MenuItemController::class, 'index']);
Route::get('/menu-items/{id}', [MenuItemController::class, 'show']);
Route::put('/menu-items/update/{id}', [MenuItemController::class, 'update']);
Route::post('/menu-items/create', [MenuItemController::class, 'store']);
Route::delete('/menu-items/delete/{id}', [MenuItemController::class, 'destroy']);

Route::get('/menu-item-orders/all', [MenuItemOrderController::class, 'index']);
Route::get('/menu-item-orders/{id}', [MenuItemOrderController::class, 'show']);
Route::put('/menu-item-orders/update/{id}', [MenuItemOrderController::class, 'update']);
Route::post('/menu-item-orders/create', [MenuItemOrderController::class, 'store']);
Route::delete('/menu-item-orders/delete/{id}', [MenuItemOrderController::class, 'destroy']);

Route::get('/orders/all', [OrderController::class, 'index']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::put('/orders/update/{id}', [OrderController::class, 'update']);
Route::post('/orders/create', [OrderController::class, 'store']);
Route::delete('/orders/delete/{id}', [OrderController::class, 'destroy']);

Route::get('/products/all', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::put('/products/update/{id}', [ProductController::class, 'update']);
Route::post('/products/create', [ProductController::class, 'store']);
Route::delete('/products/delete/{id}', [ProductController::class, 'destroy']);

Route::get('/purchases/all', [PurchaseController::class, 'index']);
Route::get('/purchases/{id}', [PurchaseController::class, 'show']);
Route::put('/purchases/update/{id}', [PurchaseController::class, 'update']);
Route::post('/purchases/create', [PurchaseController::class, 'store']);
Route::delete('/purchases/delete/{id}', [PurchaseController::class, 'destroy']);

Route::get('/purchase-items/all', [PurchaseItemController::class, 'index']);
Route::get('/purchase-items/{id}', [PurchaseItemController::class, 'show']);
Route::put('/purchase-items/update/{id}', [PurchaseItemController::class, 'update']);
Route::post('/purchase-items/create', [PurchaseItemController::class, 'store']);
Route::delete('/purchase-items/delete/{id}', [PurchaseItemController::class, 'destroy']);

Route::get('/purchase-options/all', [PurchaseOptionController::class, 'index']);
Route::get('/purchase-options/{id}', [PurchaseOptionController::class, 'show']);
Route::put('/purchase-options/update/{id}', [PurchaseOptionController::class, 'update']);
Route::post('/purchase-options/create', [PurchaseOptionController::class, 'store']);
Route::delete('/purchase-options/delete/{id}', [PurchaseOptionController::class, 'destroy']);

Route::get('/units/all', [UnitController::class, 'index']);
Route::get('/units/{id}', [UnitController::class, 'show']);
Route::put('/units/update/{id}', [UnitController::class, 'update']);
Route::post('/units/create', [UnitController::class, 'store']);
Route::delete('/units/delete/{id}', [UnitController::class, 'destroy']);
