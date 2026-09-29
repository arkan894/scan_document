<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('documents', DocumentController::class)->only([
    'index', 'create', 'store', 'show'
]);