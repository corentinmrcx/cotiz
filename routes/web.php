<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('accueil'))->name('accueil');
