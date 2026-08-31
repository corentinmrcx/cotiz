<?php

use App\Http\Controllers\ApercuCarteController;
use App\Http\Controllers\VisuelController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('reglages'))->name('accueil');
Route::view('/reglages', 'reglages')->name('reglages');
Route::get('/visuels/{saison}/{face}', [VisuelController::class, 'afficher'])
    ->whereIn('face', ['recto', 'verso'])
    ->name('visuels.afficher');
Route::get('/cartes/apercu', [ApercuCarteController::class, 'afficher'])->name('cartes.apercu');
