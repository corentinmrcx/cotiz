<?php

use App\Http\Controllers\ApercuCarteController;
use App\Http\Controllers\ClasseurModeleController;
use App\Http\Controllers\FichierCarteController;
use App\Http\Controllers\VisuelController;
use App\Models\Adhesion;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('adhesions'))->name('accueil');

Route::view('/import', 'import')->name('import');
Route::get('/import/classeur-modele', [ClasseurModeleController::class, 'telecharger'])->name('classeur.modele');

Route::view('/adhesions', 'adhesions.index')->name('adhesions');
Route::get('/adhesions/nouvelle', fn () => view('adhesions.formulaire', ['adhesion' => null]))->name('adhesions.nouvelle');
Route::get('/adhesions/{adhesion}/modifier', fn (Adhesion $adhesion) => view('adhesions.formulaire', ['adhesion' => $adhesion]))->name('adhesions.modifier');

Route::view('/reglages', 'reglages')->name('reglages');

Route::get('/visuels/{saison}/{face}', [VisuelController::class, 'afficher'])
    ->whereIn('face', ['recto', 'verso'])
    ->name('visuels.afficher');
Route::get('/cartes/apercu', [ApercuCarteController::class, 'afficher'])->name('cartes.apercu');
Route::get('/cartes/{adhesion}/{format}', [FichierCarteController::class, 'afficher'])
    ->whereIn('format', ['pdf', 'png'])
    ->name('cartes.fichier');
