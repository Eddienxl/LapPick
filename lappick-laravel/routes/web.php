<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\SpkController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SpkController::class, 'home'])->name('home');
Route::get('/catalog', [SpkController::class, 'catalog'])->name('catalog');
Route::get('/catalog/{laptop}', [SpkController::class, 'showLaptop'])->name('laptop.show');

Route::match(['get', 'post'], '/wizard', [SpkController::class, 'wizard'])->name('wizard');

Route::get('/compare', [SpkController::class, 'compare'])->name('compare');
Route::post('/compare/toggle', [SpkController::class, 'toggleCompare'])->name('compare.toggle');

Route::get('/history', [SpkController::class, 'history'])->name('history');
Route::get('/history/{history}/export-csv', [SpkController::class, 'exportHistoryCsv'])->name('history.export');

Route::get('/login', [AdminController::class, 'showLogin'])->name('login');
Route::post('/login', [AdminController::class, 'login'])->name('login.post');
Route::post('/logout', [AdminController::class, 'logout'])->name('logout');

Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
Route::post('/admin/laptops', [AdminController::class, 'storeLaptop'])->name('admin.laptops.store');
Route::delete('/admin/laptops/{laptop}', [AdminController::class, 'destroyLaptop'])->name('admin.laptops.destroy');
