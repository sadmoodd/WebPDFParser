<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EgrnController;

Route::get('/', [EgrnController::class, "index"])->name("index");


