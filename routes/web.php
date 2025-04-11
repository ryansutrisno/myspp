<?php

use App\Filament\Pages\Payment;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('admin/payment/{id}', Payment::class)->name('filament.pages.payment');
