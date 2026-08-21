<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalPageController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/catalog/{product:slug}', ProductController::class)->name('products.show');
Route::get('/privacy', LegalPageController::class)->defaults('slug', 'privacy')->name('legal.privacy');
Route::get('/consent', LegalPageController::class)->defaults('slug', 'consent')->name('legal.consent');
Route::get('/requisites', LegalPageController::class)->defaults('slug', 'requisites')->name('legal.requisites');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
