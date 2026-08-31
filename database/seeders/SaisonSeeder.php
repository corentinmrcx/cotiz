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

        Saison::query()->create([
            'libelle' => '2025-2026',
            'tarif_adulte' => 8,
            'tarif_enfant_famille' => 3,
            'tarif_enfant_seul' => 6,
            'active' => true,
            'visuel_recto' => $this->copierVisuelInitial('recto.png'),
            'visuel_verso' => $this->copierVisuelInitial('verso.png'),
        ]);
    }

    private function copierVisuelInitial(string $fichier): string
    {
        $chemin = 'visuels/'.$fichier;

        Storage::disk('data')->put($chemin, file_get_contents(__DIR__.'/visuels/'.$fichier));

        return $chemin;
    }
}
