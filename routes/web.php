<?php

use App\Http\Controllers\ApercuCarteController;
use App\Http\Controllers\ClasseurModeleController;
use App\Http\Controllers\FichierCarteController;
use App\Http\Controllers\HistoriqueController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoController;
use App\Http\Controllers\SauvegardeController;
use App\Http\Middleware\RequireAuthWhenEnabled;
use App\Models\Adhesion;
use Illuminate\Support\Facades\Route;

Route::get('/connexion', [LoginController::class, 'afficher'])->name('login');
Route::post('/connexion', [LoginController::class, 'connecter'])->name('login.connecter');
Route::post('/deconnexion', [LoginController::class, 'deconnecter'])->name('logout');

Route::middleware(RequireAuthWhenEnabled::class)->group(function () {

    Route::get('/', fn () => redirect()->route('adhesions'))->name('accueil');

    Route::view('/import', 'import')->name('import');
    Route::get('/import/classeur-modele', [ClasseurModeleController::class, 'telecharger'])->name('classeur.modele');

    Route::view('/adhesions', 'adhesions.index')->name('adhesions');
    Route::get('/adhesions/nouvelle', fn () => view('adhesions.formulaire', ['adhesion' => null]))->name('adhesions.nouvelle');
    Route::get('/adhesions/{adhesion}/modifier', fn (Adhesion $adhesion) => view('adhesions.formulaire', ['adhesion' => $adhesion]))->name('adhesions.modifier');

    Route::get('/historique', [HistoriqueController::class, 'index'])->name('historique');
    Route::get('/historique/{saison}', [HistoriqueController::class, 'saison'])->name('historique.saison');

    Route::view('/reglages', 'reglages')->name('reglages');
    Route::get('/sauvegarde', [SauvegardeController::class, 'telecharger'])->name('sauvegarde.telecharger');

    Route::get('/saisons/{saison}/logo', [LogoController::class, 'afficher'])->name('saisons.logo');
    Route::get('/cartes/apercu', [ApercuCarteController::class, 'afficher'])->name('cartes.apercu');
    Route::get('/cartes/{adhesion}/{format}', [FichierCarteController::class, 'afficher'])
        ->whereIn('format', ['pdf', 'png'])
        ->name('cartes.fichier');

});
