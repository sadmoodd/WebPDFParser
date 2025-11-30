<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EgrnController;
use App\Http\Controllers\AboutHelpController;

Route::get('/', [EgrnController::class, "index"])->name("index");

Route::get('/about', [AboutHelpController::class, "about"])->name("about");
Route::get("/help", [AboutHelpController::class, "help"])->name("help");


Route::post('/api/parse-egrn', [EgrnController::class, 'processEgrn']);
Route::get('/api/download/{filename}', [EgrnController::class, 'downloadResult']);
