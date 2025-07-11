<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DishCategoryController;
use App\Http\Controllers\IngredientGroupController;
use App\Http\Controllers\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\IngredientTypeController;
use App\Http\Controllers\IngredientController;
use App\Http\Controllers\DishController;
use App\Http\Controllers\DishGroupController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\IngredientCategoryController;
use App\Http\Controllers\InventoryActController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\MenuItemOrderController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductGroupController;
use App\Http\Controllers\PurchaseActController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseItemController;
use App\Http\Controllers\PurchaseOptionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SaleActController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerifyEmailController;
use App\Http\Controllers\WriteOffActController;
use App\Models\DishCategory;
use App\Models\IngredientCategory;
use App\Models\ProductCategory;
use App\Models\Storage\InventoryAct;

Route::controller(AuthController::class)
    ->prefix('auth')->group( function(){
        Route::post('register','register');
        Route::post('login','login');
        Route::get('authorization-needed','authorization_needed')->name('login');

        Route::post('forgot-password', 'forgot_password')
            ->middleware('guest')->name('password.email');
        Route::post('reset-password', 'reset_password')
            ->middleware('guest')->name('password.update');
                
        Route::middleware(['auth:sanctum'])->group(function(){
            Route::put('update-password', 'update_password');
            Route::put('update/{id}', 'update');
            Route::get('current', 'current');
            Route::get('verification-needed', 'verification_needed')->name('verification.notice');
            Route::get('verify/{id}/{hash}', 'verify')
                ->middleware(['signed'])
                ->name('verification.verify');
            Route::get('resend', 'resend')->name('verification.resend');
            Route::post('logout', 'logout');
            Route::post('delete', 'destroy');
        });
    });


Route::middleware(['auth:sanctum', 'verified'])->group(function(){

    Route::controller(ProjectController::class)->prefix('projects')->group(function() {
        Route::get('all', 'all_user_projects');
        Route::get('{id}', 'show');
        Route::post('{id}/publish', 'publish_project');
        Route::post('{id}/unpublish', 'unpublish_project');
        Route::get('{id}/download', 'download');
        Route::post('{id}/upload', 'upload');
        Route::put('update/{project_id}', 'update');
        Route::post('create', 'store');
        Route::post('{id}/invite-user', 'invite_user');
        Route::delete('delete/{project_id}', 'destroy');
        
        Route::post('{id}/upload-logo', 'upload_logo');
        Route::post('{id}/upload-backdrop', 'upload_backdrop');
    });
    
    getProjectEntitiesRoutes('project/{project_id}');
    
});

getProjectEntitiesRoutes('public/project/{project_id}');

Route::controller(ProjectController::class)->prefix('projects/public')->group(function(){
    Route::get('all', 'all_public_projects');
    Route::get('{id}', 'show');
});

function getProjectEntitiesRoutes(string $prefix){
    return Route::prefix($prefix)->group(function(){
        Route::controller(UserController::class)->prefix('users')->group(function() {
            Route::put('assign-role/{id}', 'assign_role');
            Route::delete('remove/{id}', 'remove_from_project');
            Route::post('invite', 'invite_to_project');
            Route::get('all', 'index');
        });
    
        Route::controller(RoleController::class)
            ->prefix('roles')->group( function(){
                Route::get('all','all');
                Route::get('permissions/all','permissions');
            });

        Route::controller(IngredientController::class)->prefix('ingredients')->group(function() {
            Route::get('all', 'index');
            Route::get('{id}', 'show');
            Route::put('update/{id}', 'update');
            Route::post('create', 'store');
            Route::delete('delete/{id}', 'destroy');
        
            Route::get('with-products/all', 'index_loaded');
            Route::get('with-products/{id}', 'show_loaded');
            Route::put('with-products/update/{id}', 'update_loaded');
            Route::post('with-products/create', 'store_loaded');
        
            Route::get('with-purchase-options/{id}', 'show_with_purchase_options');
        });
    
        Route::controller(IngredientCategoryController::class)->prefix('ingredient-categories')->group(function() {
            Route::get('all', 'index');
            Route::put('update/{id}', 'update');
            Route::post('create', 'store');
            Route::delete('delete/{id}', 'destroy');
            Route::get('{id}', 'show');
    
            Route::get('with-ingredients/all', 'index_loaded');
            Route::put('with-ingredients/update/{id}', 'update_loaded');
            Route::post('with-ingredients/create', 'store_loaded');
            Route::get('with-ingredients/{id}', 'show_loaded');
        });
    
        Route::controller(IngredientGroupController::class)->prefix('ingredient-groups')->group(function() {
    
            Route::get('with-ingredients/all', 'index_loaded');
            Route::put('with-ingredients/update/{id}', 'update_loaded');
            Route::post('with-ingredients/create', 'store_loaded');
            Route::get('with-ingredients/{id}', 'show_loaded');
    
            Route::get('all', 'index');
            Route::put('update/{id}', 'update');
            Route::post('create', 'store');
            Route::delete('delete/{id}', 'destroy');
            Route::get('{id}', 'show');
        });
    
        Route::controller(DishController::class)->prefix('dishes')->group(function() {
            Route::get('all', 'index');
            Route::put('update/{id}', 'update');
            Route::post('create', 'store');
            Route::get('{id}', 'show');
            Route::delete('delete/{id}', 'destroy');
            Route::get('with-ingredients/all', 'index_loaded');
            Route::put('with-ingredients/update/{id}', 'update_loaded');
            Route::post('with-ingredients/create', 'store_loaded');
            Route::get('with-ingredients/{id}', 'show_loaded');
            Route::get('with-purchase-options/all', 'index_with_purchase_options');
            Route::get('with-purchase-options/{id}', 'show_with_purchase_options');
            
            Route::post('{id}/upload-image', 'upload_image');
        });
    
        Route::controller(DishCategoryController::class)->prefix('dish-categories')->group(function() {
            Route::get('all', 'index');
            Route::put('update/{id}', 'update');
            Route::post('create', 'store');
            Route::delete('delete/{id}', 'destroy');
            Route::get('{id}', 'show');
    
            Route::get('with-dishes/all', 'index_loaded');
            Route::put('with-dishes/update/{id}', 'update_loaded');
            Route::post('with-dishes/create', 'store_loaded');
            Route::get('with-dishes/{id}', 'show_loaded');
    
        });
    
        Route::controller(DishGroupController::class)->prefix('dish-groups')->group(function() {
            Route::get('all', 'index');
            Route::put('update/{id}', 'update');
            Route::post('create', 'store');
            Route::delete('delete/{id}', 'destroy');
            Route::get('{id}', 'show');
    
            Route::get('with-dishes/all', 'index_loaded');
            Route::put('with-dishes/update/{id}', 'update_loaded');
            Route::post('with-dishes/create', 'store_loaded');
            Route::get('with-dishes/{id}', 'show_loaded');
        });
    
        Route::controller(IngredientTypeController::class)->prefix('ingredient-types')->group(function() {
            Route::get('all', 'index');
            Route::get('{id}', 'show');
            Route::put('update/{id}', 'update');
            Route::post('create', 'store');
            Route::delete('delete/{id}', 'destroy');
        });
    
        Route::controller(DistributorController::class)->prefix('distributors')->group(function() {
            Route::get('all', 'index');
            Route::get('{id}', 'show');
            Route::put('update/{id}', 'update');
            Route::post('create', 'store');
            Route::delete('delete/{id}', 'destroy');
            Route::get('with-purchase-options/all', 'index_loaded');
            Route::get('with-purchase-options/{id}', 'show_loaded');
            Route::put('with-purchase-options/update/{id}', 'update_loaded');
            Route::post('with-purchase-options/create', 'store_loaded');
            Route::post('with-purchase-options/{id}/upload-options-file', 'upload_options_file');
        });
    
        Route::controller(ProductController::class)->prefix('products')->group(function() {
            Route::get('all', 'index');
            Route::post('create', 'store');
            Route::get('{id}', 'show');
            Route::put('update/{id}', 'update');
            Route::delete('delete/{id}', 'destroy');
            Route::get('with-purchase-options/all', 'index_with_purchase_options');
            Route::post('with-purchase-options/create', 'store_with_purchase_options');
            Route::put('with-purchase-options/update/{id}', 'update_with_purchase_options');
            Route::get('with-purchase-options/{id}', 'show_with_purchase_options');
        });
    
        Route::controller(ProductCategoryController::class)->prefix('product-categories')->group(function() {
            Route::get('all', 'index');
            Route::put('update/{id}', 'update');
            Route::post('create', 'store');
            Route::delete('delete/{id}', 'destroy');
            Route::get('{id}', 'show');
    
            Route::get('with-products/all', 'index_loaded');
            Route::put('with-products/update/{id}', 'update_loaded');
            Route::post('with-products/create', 'store_loaded');
            Route::get('with-products/{id}', 'show_loaded');
        });
    
        Route::controller(ProductGroupController::class)->prefix('product-groups')->group(function() {
            Route::get('all', 'index');
            Route::get('{id}', 'show');
            Route::put('update/{id}', 'update');
            Route::post('create', 'store');
            Route::delete('delete/{id}', 'destroy');
    
            Route::get('with-products/all', 'index_loaded');
            Route::put('with-products/update/{id}', 'update_loaded');
            Route::post('with-products/create', 'store_loaded');
            Route::get('with-products/{id}', 'show_loaded');
        });
    
        Route::controller(PurchaseOptionController::class)->prefix('purchase-options')->group(function() {
            Route::get('all', 'index');
            Route::get('{id}', 'show');
            Route::put('update/{id}', 'update');
            Route::post('create', 'store');
            Route::get('with-products/all', 'index_loaded');
            Route::get('with-products/{id}', 'show_loaded');
            Route::put('with-products/update/{id}', 'update_loaded');
            Route::post('with-products/create', 'store_loaded');
            Route::delete('delete/{id}', 'destroy');
        });
    
        Route::controller(InventoryActController::class)->prefix('inventory-acts')->group(function() {
            Route::get('all', 'index');
            Route::get('{id}', 'show');
            Route::put('update/{id}', 'update');
            Route::post('create', 'store');
            Route::get('with-items/all', 'index_loaded');
            Route::get('with-items/{id}', 'show_loaded');
            Route::put('with-items/update/{id}', 'update_loaded');
            Route::post('with-items/create', 'store_loaded');
            Route::delete('delete/{id}', 'destroy');
        });
    
        Route::controller(WriteOffActController::class)->prefix('write-off-acts')->group(function() {
            Route::get('all', 'index');
            Route::get('{id}', 'show');
            Route::put('update/{id}', 'update');
            Route::post('create', 'store');
            Route::get('with-items/all', 'index_loaded');
            Route::get('with-items/{id}', 'show_loaded');
            Route::put('with-items/update/{id}', 'update_loaded');
            Route::post('with-items/create', 'store_loaded');
            Route::delete('delete/{id}', 'destroy');
        });
    
        Route::controller(PurchaseActController::class)->prefix('purchase-acts')->group(function() {
            Route::get('all', 'index');
            Route::get('{id}', 'show');
            Route::put('update/{id}', 'update');
            Route::post('create', 'store');
            Route::get('with-items/all', 'index_loaded');
            Route::get('with-items/{id}', 'show_loaded');
            Route::put('with-items/update/{id}', 'update_loaded');
            Route::post('with-items/create', 'store_loaded');
            Route::delete('delete/{id}', 'destroy');
        });
    
        Route::controller(SaleActController::class)->prefix('sale-acts')->group(function() {
            Route::get('all', 'index');
            Route::get('{id}', 'show');
            Route::put('update/{id}', 'update');
            Route::post('create', 'store');
            Route::get('with-items/all', 'index_loaded');
            Route::get('with-items/{id}', 'show_loaded');
            Route::put('with-items/update/{id}', 'update_loaded');
            Route::post('with-items/create', 'store_loaded');
            Route::delete('delete/{id}', 'destroy');
        });
    
        Route::controller(UnitController::class)->prefix('units')->group(function() {
            Route::get('all', 'index');
            Route::get('{id}', 'show');
            Route::put('update/{id}', 'update');
            Route::post('create', 'store');
            Route::delete('delete/{id}', 'destroy');
        });
    });
}