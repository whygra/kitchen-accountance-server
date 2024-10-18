<?php

use App\Http\Controllers\DishCategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\IngredientTypeController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\DishController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\IngredientCategoryController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\MenuItemOrderController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseItemController;
use App\Http\Controllers\PurchaseOptionController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Models\DishCategory;
use App\Models\IngredientCategory;
use App\Models\ProductCategory;

Route::post('users/register', [UserController::class, 'register']);
Route::post('users/login', [UserController::class, 'login']);
Route::get('users/authorization-needed', [UserController::class, 'authorization_needed'])->name('login');
Route::post('users/verify/{id}', [UserController::class, 'verify'])->name('verification.verify');
Route::post('users/resend', [UserController::class, 'resend'])->name('verification.resend');

Route::middleware(['auth:sanctum'])->group(function(){
    Route::post('users/logout', [UserController::class, 'logout']);
    Route::get('users/current', [UserController::class, 'current']);
    Route::put('users/assign-roles/{id}', [UserController::class, 'assign_roles']);
    Route::put('users/update-password', [UserController::class, 'update_password']);
    Route::get('users/roles/all', [UserController::class, 'get_roles']);
    Route::get('users/all', [UserController::class, 'index']);

    Route::get('/ingredients/all', [IngredientController::class, 'index']);
    Route::get('/ingredients/{id}', [IngredientController::class, 'show']);
    Route::put('/ingredients/update/{id}', [IngredientController::class, 'update']);
    Route::post('/ingredients/create', [IngredientController::class, 'store']);
    Route::delete('/ingredients/delete/{id}', [IngredientController::class, 'destroy']);
    
    Route::get('/ingredients/with-products/all', [IngredientController::class, 'index_loaded']);
    Route::get('/ingredients/with-products/{id}', [IngredientController::class, 'show_loaded']);
    Route::put('/ingredients/with-products/update/{id}', [IngredientController::class, 'update_loaded']);
    Route::post('/ingredients/with-products/create', [IngredientController::class, 'store_loaded']);

    Route::get('/ingredients/with-purchase-options/{id}', [IngredientController::class, 'show_with_purchase_options']);
    
    Route::get('/ingredient-categories/all', [IngredientCategoryController::class, 'index']);
    Route::get('/ingredient-categories/{id}', [IngredientCategoryController::class, 'show']);
    Route::put('/ingredient-categories/update/{id}', [IngredientCategoryController::class, 'update']);
    Route::post('/ingredient-categories/create', [IngredientCategoryController::class, 'store']);
    
    Route::get('/dishes/all', [DishController::class, 'index']);
    Route::get('/dishes/{id}', [DishController::class, 'show']);
    Route::put('/dishes/update/{id}', [DishController::class, 'update']);
    Route::post('/dishes/create', [DishController::class, 'store']);
    Route::delete('/dishes/delete/{id}', [DishController::class, 'destroy']);
    Route::get('/dishes/with-ingredients/all', [DishController::class, 'index_loaded']);
    Route::get('/dishes/with-ingredients/{id}', [DishController::class, 'show_loaded']);
    Route::put('/dishes/with-ingredients/update/{id}', [DishController::class, 'update_loaded']);
    Route::post('/dishes/with-ingredients/create', [DishController::class, 'store_loaded']);
    Route::get('/dishes/with-purchase-options/all', [DishController::class, 'index_with_purchase_options']);
    Route::get('/dishes/with-purchase-options/{id}', [DishController::class, 'show_with_purchase_options']);
    
    Route::post('/dishes/upload-image', [DishController::class, 'upload_image']);

    Route::get('/dish-categories/all', [DishCategoryController::class, 'index']);
    Route::get('/dish-categories/{id}', [DishCategoryController::class, 'show']);
    Route::get('/dish-categories/with-dishes/all', [DishCategoryController::class, 'index_loaded']);
    Route::get('/dish-categories/with-dishes/{id}', [DishCategoryController::class, 'show_loaded']);
    Route::put('/dish-categories/update/{id}', [DishCategoryController::class, 'update']);
    Route::post('/dish-categories/create', [DishCategoryController::class, 'store']);
    Route::delete('/dish-categories/delete/{id}', [DishCategoryController::class, 'destroy']);
    
    Route::get('/ingredient-types/all', [IngredientTypeController::class, 'index']);
    Route::get('/ingredient-types/{id}', [IngredientTypeController::class, 'show']);
    Route::put('/ingredient-types/update/{id}', [IngredientTypeController::class, 'update']);
    Route::post('/ingredient-types/create', [IngredientTypeController::class, 'store']);
    Route::delete('/ingredient-types/delete/{id}', [IngredientTypeController::class, 'destroy']);
    
    Route::get('/distributors/all', [DistributorController::class, 'index']);
    Route::get('/distributors/{id}', [DistributorController::class, 'show']);
    Route::put('/distributors/update/{id}', [DistributorController::class, 'update']);
    Route::post('/distributors/create', [DistributorController::class, 'store']);
    Route::delete('/distributors/delete/{id}', [DistributorController::class, 'destroy']);
    Route::get('/distributors/with-purchase-options/all', [DistributorController::class, 'index_loaded']);
    Route::get('/distributors/with-purchase-options/{id}', [DistributorController::class, 'show_loaded']);
    Route::put('/distributors/with-purchase-options/update/{id}', [DistributorController::class, 'update_loaded']);
    Route::post('/distributors/with-purchase-options/create', [DistributorController::class, 'store_loaded']);
    Route::post('/distributors/with-purchase-options/upload-options-file', [DistributorController::class, 'upload_options_file']);
        
    Route::get('/products/all', [ProductController::class, 'index']);
    Route::post('/products/create', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::put('/products/update/{id}', [ProductController::class, 'update']);
    Route::delete('/products/delete/{id}', [ProductController::class, 'destroy']);
    Route::get('/products/with-purchase-options/all', [ProductController::class, 'index_with_purchase_options']);
    Route::post('/products/with-purchase-options/create', [ProductController::class, 'store_with_purchase_options']);
    Route::put('/products/with-purchase-options/update/{id}', [ProductController::class, 'update_with_purchase_options']);
    Route::get('/products/with-purchase-options/{id}', [ProductController::class, 'show_with_purchase_options']);
    
    Route::get('/product-categories/all', [ProductCategoryController::class, 'index']);
    Route::get('/product-categories/{id}', [ProductCategoryController::class, 'show']);
    Route::put('/product-categories/update/{id}', [ProductCategoryController::class, 'update']);
    Route::post('/product-categories/create', [ProductCategoryController::class, 'store']);
    Route::delete('/product-categories/delete/{id}', [ProductCategoryController::class, 'destroy']);
    
    Route::get('/purchase-options/all', [PurchaseOptionController::class, 'index']);
    Route::get('/purchase-options/{id}', [PurchaseOptionController::class, 'show']);
    Route::put('/purchase-options/update/{id}', [PurchaseOptionController::class, 'update']);
    Route::post('/purchase-options/create', [PurchaseOptionController::class, 'store']);
    Route::get('/purchase-options/with-products/all', [PurchaseOptionController::class, 'index_loaded']);
    Route::get('/purchase-options/with-products/{id}', [PurchaseOptionController::class, 'show_loaded']);
    Route::put('/purchase-options/with-products/update/{id}', [PurchaseOptionController::class, 'update_loaded']);
    Route::post('/purchase-options/with-products/create', [PurchaseOptionController::class, 'store_loaded']);
    Route::delete('/purchase-options/delete/{id}', [PurchaseOptionController::class, 'destroy']);
    
    Route::get('/units/all', [UnitController::class, 'index']);
    Route::get('/units/{id}', [UnitController::class, 'show']);
    Route::put('/units/update/{id}', [UnitController::class, 'update']);
    Route::post('/units/create', [UnitController::class, 'store']);
    Route::delete('/units/delete/{id}', [UnitController::class, 'destroy']);
});
