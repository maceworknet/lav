<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\FavoriteController;

// Storefront routes
Route::get('/', [FrontendController::class, 'home'])->name('home');
Route::get('/kategori/{slug}', [FrontendController::class, 'category'])->name('category');
Route::get('/urun/{slug}', [FrontendController::class, 'product'])->name('product');
Route::get('/arama', [FrontendController::class, 'search'])->name('search');
Route::get('/handpicked-products', [FrontendController::class, 'handpickedProducts'])->name('handpicked.products');

// Favorites routes
Route::get('/favoriler', [FavoriteController::class, 'index'])->name('favorites.index');
Route::post('/favoriler/toggle', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

// Cart routes
Route::get('/sepet', [CartController::class, 'index'])->name('cart.index');
Route::get('/sepet/json', [CartController::class, 'getCartJson'])->name('cart.json');
Route::post('/sepet/ekle', [CartController::class, 'add'])->name('cart.add');
Route::post('/sepet/guncelle/{id}', [CartController::class, 'update'])->name('cart.update');
Route::post('/sepet/sil/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/sepet/kupon-uygula', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
Route::post('/sepet/kupon-kaldir', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

// Checkout routes
Route::get('/odeme', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/odeme/tamamla', [CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/siparis-basarili/{order_number}', [CheckoutController::class, 'success'])->name('checkout.success');
Route::get('/teslimat-saatleri', [CheckoutController::class, 'getSlots'])->name('checkout.slots');
Route::get('/taksit-secenekleri', [FrontendController::class, 'installments'])->name('installments.info');

// Tracking & Static Pages
Route::get('/siparis-takip', [FrontendController::class, 'tracking'])->name('tracking');
Route::get('/sayfa/{slug}', [FrontendController::class, 'page'])->name('page');
Route::get('/blog', [FrontendController::class, 'blogList'])->name('blog.list');
Route::get('/blog/{slug}', [FrontendController::class, 'blogDetail'])->name('blog.detail');
Route::get('/iletisim', [FrontendController::class, 'contact'])->name('contact');

// SEO assets
Route::get('/sitemap.xml', [FrontendController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [FrontendController::class, 'robots'])->name('robots');

// Customer Auth Routes
Route::middleware('guest:customer')->group(function () {
    Route::get('/giris', [CustomerAuthController::class, 'showLoginForm'])->name('customer.login');
    Route::post('/giris', [CustomerAuthController::class, 'login']);
    Route::get('/kayit', function () {
        return redirect()->route('customer.login');
    })->name('customer.register');
    Route::post('/kayit', [CustomerAuthController::class, 'register']);

    // Google Auth Routes
    Route::get('/auth/google', [CustomerAuthController::class, 'redirectToGoogle'])->name('customer.auth.google');
    Route::get('/auth/google/callback', [CustomerAuthController::class, 'handleGoogleCallback'])->name('customer.auth.google.callback');

    // Password Reset Routes
    Route::get('/sifremi-unuttum', [CustomerAuthController::class, 'showLinkRequestForm'])->name('customer.password.request');
    Route::post('/sifremi-unuttum', [CustomerAuthController::class, 'sendResetLinkEmail'])->name('customer.password.email');
    Route::get('/sifre-sifirla/{token}', [CustomerAuthController::class, 'showResetForm'])->name('customer.password.reset');
    Route::post('/sifre-sifirla', [CustomerAuthController::class, 'reset'])->name('customer.password.update');
});

Route::middleware('auth:customer')->group(function () {
    Route::post('/cikis', [CustomerAuthController::class, 'logout'])->name('customer.logout');
    Route::get('/hesabim', [CustomerAccountController::class, 'index'])->name('customer.account');
    Route::post('/hesabim/bilgileri-guncelle', [CustomerAccountController::class, 'updateProfile'])->name('customer.account.update_profile');
    Route::post('/hesabim/sifre-guncelle', [CustomerAccountController::class, 'updatePassword'])->name('customer.account.update_password');
    Route::post('/hesabim/adres-ekle', [CustomerAccountController::class, 'addAddress'])->name('customer.account.add_address');
    Route::put('/hesabim/adres-guncelle/{id}', [CustomerAccountController::class, 'updateAddress'])->name('customer.account.update_address');
    Route::delete('/hesabim/adres-sil/{id}', [CustomerAccountController::class, 'deleteAddress'])->name('customer.account.delete_address');
});

