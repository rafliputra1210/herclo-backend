<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BannerController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\GalleryController;
use App\Http\Controllers\API\ArticleController;
use App\Http\Controllers\API\TestimonialController;

// ============================================================
// RUTE PUBLIK (Tidak perlu login)
// ============================================================

// Autentikasi
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Produk & Kategori (bisa diakses siapa saja)
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{slug}', [ProductController::class, 'show']);
Route::get('/banners', [BannerController::class, 'index']); // <-- Tambahkan ini
Route::get('/galleries', [GalleryController::class, 'index']);
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/testimonials', [TestimonialController::class, 'index']);
Route::post('/checkout', [OrderController::class, 'store']);
Route::get('/settings', [\App\Http\Controllers\API\SettingController::class, 'index']);
Route::post('/midtrans-callback', [\App\Http\Controllers\API\OrderController::class, 'callback']);
Route::get('/my-orders', [\App\Http\Controllers\API\OrderController::class, 'myOrders']);
Route::post('/promo/validate', [\App\Http\Controllers\API\PromoController::class, 'validatePromo']);
Route::get('/promos', [\App\Http\Controllers\API\PromoController::class, 'activePromos']);
Route::get('/articles', [\App\Http\Controllers\API\ArticleController::class, 'index']);
Route::get('/articles/{slug}', [\App\Http\Controllers\API\ArticleController::class, 'show']);
Route::get('/team', [\App\Http\Controllers\API\TeamMemberController::class, 'index']);
Route::get('/company-profile', [\App\Http\Controllers\API\CompanyProfileController::class, 'show']);
// ============================================================
// RUTE PROTEKSI (Harus login dengan Token Sanctum)
// ============================================================
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/admin/categories', [CategoryController::class, 'store']);
    Route::put('/admin/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/admin/categories/{id}', [CategoryController::class, 'destroy']);
    Route::get('/admin/orders', [OrderController::class, 'index']);
    Route::put('/admin/orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::post('/admin/products', [ProductController::class, 'store']);
    Route::put('/admin/products/{id}', [ProductController::class, 'update']);
    Route::delete('/admin/products/{id}', [ProductController::class, 'destroy']);
    Route::get('/admin/galleries', [GalleryController::class, 'index']);
    Route::post('/admin/galleries', [GalleryController::class, 'store']);
    Route::delete('/admin/galleries/{id}', [GalleryController::class, 'destroy']);
    Route::get('/admin/articles', [ArticleController::class, 'index']);
    Route::post('/admin/articles', [ArticleController::class, 'store']);
    Route::put('/admin/articles/{id}', [ArticleController::class, 'update']);
    Route::delete('/admin/articles/{id}', [ArticleController::class, 'destroy']);
    Route::get('/admin/testimonials', [TestimonialController::class, 'index']);
    Route::post('/admin/testimonials', [TestimonialController::class, 'store']);
    Route::put('/admin/testimonials/{id}', [TestimonialController::class, 'update']);
    Route::delete('/admin/testimonials/{id}', [TestimonialController::class, 'destroy']);
    Route::get('/admin/banners', [BannerController::class, 'index']);
    Route::post('/admin/banners', [BannerController::class, 'store']);
    Route::put('/admin/banners/{id}/status', [BannerController::class, 'updateStatus']);
    Route::delete('/admin/banners/{id}', [BannerController::class, 'destroy']);
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart', [CartController::class, 'store']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    Route::get('/my-orders', [OrderController::class, 'myOrders']);
    Route::post('/admin/settings', [\App\Http\Controllers\API\SettingController::class, 'update']);
    Route::get('/admin/customers', [\App\Http\Controllers\API\CustomerController::class, 'index']);
    Route::get('/admin/promos', [\App\Http\Controllers\API\PromoController::class, 'index']);
    Route::post('/admin/promos', [\App\Http\Controllers\API\PromoController::class, 'store']);
    Route::delete('/admin/promos/{id}', [\App\Http\Controllers\API\PromoController::class, 'destroy']);
    Route::post('/admin/team', [\App\Http\Controllers\API\TeamMemberController::class, 'store']);
    Route::post('/admin/team/{id}', [\App\Http\Controllers\API\TeamMemberController::class, 'update']);
    Route::delete('/admin/team/{id}', [\App\Http\Controllers\API\TeamMemberController::class, 'destroy']);
    Route::post('/admin/company-profile', [\App\Http\Controllers\API\CompanyProfileController::class, 'update']);
    Route::post('/admin/inventory/scan', [\App\Http\Controllers\API\InventoryController::class, 'scan']);   
    // Cek profil user yang sedang login
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    });

    // --------------------------------------------------------
    // RUTE KHUSUS ADMIN
    // --------------------------------------------------------
    Route::prefix('admin')->group(function () {
        Route::get('/products', [ProductController::class, 'index']);
        Route::post('/products', [ProductController::class, 'store']);
    });
});
