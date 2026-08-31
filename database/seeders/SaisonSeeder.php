<?php

namespace Database\Seeders;

use App\Models\Saison;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class SaisonSeeder extends Seeder
{
    public function run(): void
    {
        if (Saison::query()->exists()) {
            return;
        }

        $saison = Saison::query()->create([
            'libelle' => '2025-2026',
            'tarif_adulte' => 8,
            'tarif_enfant_famille' => 3,
            'tarif_enfant_seul' => 6,
            'active' => true,
        ]);

        $saison->update(['logo' => $this->copierLogoInitial($saison)]);
    }

    private function copierLogoInitial(Saison $saison): string
    {
        $chemin = "visuels/saison-{$saison->id}-logo.png";

        Storage::disk('data')->put($chemin, file_get_contents(__DIR__.'/visuels/logo.png'));

        return $chemin;
    }
}
