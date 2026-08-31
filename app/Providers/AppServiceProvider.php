<?php

namespace App\Providers;

use App\Services\EtatSauvegarde;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('components.layouts.app', function ($vue) {
            $vue->with('modificationsNonExportees', app(EtatSauvegarde::class)->modificationsNonExportees());
        });
    }
}
