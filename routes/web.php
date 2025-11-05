<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SnapshotKondisiController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::view('/', 'index')->name('dashboard');

    Route::middleware('role:Admin')->group(function () {
        Route::view('/admin/barang', 'admin.barang.index')->name('admin.barang.index');
    });
    
    Route::middleware('role:Super Admin')->group(function () {
        Route::view('superadmin/user', 'superadmin.user.index')->name('superadmin.user.index');
        Route::view('superadmin/barang', 'superadmin.barang.index')->name('superadmin.barang.index');
        Route::view('superadmin/kategori', 'superadmin.kategori.index')->name('superadmin.kategori.index');
        Route::view('superadmin/logs', 'superadmin.logs.index')->name('superadmin.logs.index');
        Route::get('snapshot-kondisi', SnapshotKondisiController::class)->name('snapshot-kondisi');
    });
});