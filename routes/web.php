<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\IngredientController;
use App\Http\Controllers\IngredientProductController;
use App\Http\Controllers\IngredientTypeController;
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

Route::get('/ingredients', [IngredientController::class, 'index']);
Route::get('/ingredients/{id}', [IngredientController::class, 'show']);
Route::put('/ingredients/update/{id}', [IngredientController::class, 'show']);
Route::post('/ingredients/create', [IngredientController::class, 'store']);
Route::delete('/ingredients/delete/{id}', [IngredientController::class, 'destroy']);

Route::get('/ingredient-products/all', [IngredientProductController::class, 'index']);
Route::get('/ingredient-products/{id}', [IngredientProductController::class, 'show']);
Route::put('/ingredient-products/update/{id}', [IngredientProductController::class, 'show']);
Route::post('/ingredient-products/create', [IngredientProductController::class, 'store']);
Route::delete('/ingredient-products/delete/{id}', [IngredientProductController::class, 'destroy']);

Route::get('/ingredient-types/all', [IngredientTypeController::class, 'index']);
Route::get('/ingredient-types/{id}', [IngredientTypeController::class, 'show']);
Route::put('/ingredient-types/update/{id}', [IngredientTypeController::class, 'show']);
Route::post('/ingredient-types/create', [IngredientTypeController::class, 'store']);
Route::delete('/ingredient-types/delete/{id}', [IngredientTypeController::class, 'destroy']);

Route::get('/dishes/all', [DishController::class, 'index']);
Route::get('/dishes/{id}', [DishController::class, 'show']);
Route::put('/dishes/update/{id}', [DishController::class, 'show']);
Route::post('/dishes/create', [DishController::class, 'store']);
Route::delete('/dishes/delete/{id}', [DishController::class, 'destroy']);

Route::get('/distributors/all', [DistributorController::class, 'index']);
Route::get('/distributors/{id}', [DistributorController::class, 'show']);
Route::put('/distributors/update/{id}', [DistributorController::class, 'show']);
Route::post('/distributors/create', [DistributorController::class, 'store']);
Route::delete('/distributors/delete/{id}', [DistributorController::class, 'destroy']);

Route::get('/menu-items/all', [MenuItemController::class, 'index']);
Route::get('/menu-items/{id}', [MenuItemController::class, 'show']);
Route::put('/menu-items/update/{id}', [MenuItemController::class, 'show']);
Route::post('/menu-items/create', [MenuItemController::class, 'store']);
Route::delete('/menu-items/delete/{id}', [MenuItemController::class, 'destroy']);

Route::get('/menu-item-orders/all', [MenuItemOrderController::class, 'index']);
Route::get('/menu-item-orders/{id}', [MenuItemOrderController::class, 'show']);
Route::put('/menu-item-orders/update/{id}', [MenuItemOrderController::class, 'show']);
Route::post('/menu-item-orders/create', [MenuItemOrderController::class, 'store']);
Route::delete('/menu-item-orders/delete/{id}', [MenuItemOrderController::class, 'destroy']);

Route::get('/orders/all', [OrderController::class, 'index']);
Route::get('/orders/{id}', [OrderController::class, 'show']);
Route::put('/orders/update/{id}', [OrderController::class, 'show']);
Route::post('/orders/create', [OrderController::class, 'store']);
Route::delete('/orders/delete/{id}', [OrderController::class, 'destroy']);

Route::get('/products/all', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::put('/products/update/{id}', [ProductController::class, 'show']);
Route::post('/products/create', [ProductController::class, 'store']);
Route::delete('/products/delete/{id}', [ProductController::class, 'destroy']);

Route::get('/purchases/all', [PurchaseController::class, 'index']);
Route::get('/purchases/{id}', [PurchaseController::class, 'show']);
Route::put('/purchases/update/{id}', [PurchaseController::class, 'show']);
Route::post('/purchases/create', [PurchaseController::class, 'store']);
Route::delete('/purchases/delete/{id}', [PurchaseController::class, 'destroy']);

Route::get('/purchase-items/all', [PurchaseItemController::class, 'index']);
Route::get('/purchase-items/{id}', [PurchaseItemController::class, 'show']);
Route::put('/purchase-items/update/{id}', [PurchaseItemController::class, 'show']);
Route::post('/purchase-items/create', [PurchaseItemController::class, 'store']);
Route::delete('/purchase-items/delete/{id}', [PurchaseItemController::class, 'destroy']);

Route::get('/purchase-options/all', [PurchaseOptionController::class, 'index']);
Route::get('/purchase-options/{id}', [PurchaseOptionController::class, 'show']);
Route::put('/purchase-options/update/{id}', [PurchaseOptionController::class, 'show']);
Route::post('/purchase-options/create', [PurchaseOptionController::class, 'store']);
Route::delete('/purchase-options/delete/{id}', [PurchaseOptionController::class, 'destroy']);

Route::get('/units/all', [UnitController::class, 'index']);
Route::get('/units/{id}', [UnitController::class, 'show']);
Route::put('/units/update/{id}', [UnitController::class, 'show']);
Route::post('/units/create', [UnitController::class, 'store']);
Route::delete('/units/delete/{id}', [UnitController::class, 'destroy']);
