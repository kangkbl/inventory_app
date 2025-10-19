<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
})->name('testindex');

Route::view('superadmin/user', 'superadmin.user.index')->name('superadmin.user.index');
Route::view('superadmin/barang', 'superadmin.barang.index')->name('superadmin.barang.index');
Route::view('superadmin/kategori', 'superadmin.kategori.index')->name('superadmin.kategori.index');