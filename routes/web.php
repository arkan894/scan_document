<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;

Route::get('/', [DocumentController::class, 'create'])->name('documents.create');

Route::resource('documents', DocumentController::class)->only([
    'index', 'store', 'show'
]);