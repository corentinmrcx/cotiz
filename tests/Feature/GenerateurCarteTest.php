<?php

namespace Tests\Feature;

use App\Models\Adhesion;
use App\Models\Saison;
use App\Services\GenerateurCarte;
use Database\Seeders\ReglageSeeder;
use Database\Seeders\SaisonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GenerateurCarteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('data');
        $this->seed([SaisonSeeder::class, ReglageSeeder::class]);
    }

    public function test_une_carte_est_generee_en_pdf_et_png_avec_un_nom_normalise(): void
    {
        $adhesion = Adhesion::query()->create([
            'saison_id' => Saison::active()->id,
            'numero' => '2526-001',
            'nom' => 'Lefèvre-Dupont',
            'prenom' => 'Éléonore',
            'nb_adultes' => 2,
            'nb_enfants_famille' => 1,
            'nb_enfants_seuls' => 0,
            'cotisation_calculee' => 19,
        ]);

        app(GenerateurCarte::class)->generer($adhesion);

        $adhesion->refresh();

        $this->assertSame('cartes/2025-2026/CarteAdherent_FoyerSoudron_2025-2026_LEFEVRE-DUPONT-Eleonore.pdf', $adhesion->chemin_pdf);
        $this->assertSame('cartes/2025-2026/CarteAdherent_FoyerSoudron_2025-2026_LEFEVRE-DUPONT-Eleonore.png', $adhesion->chemin_png);
        Storage::disk('data')->assertExists($adhesion->chemin_pdf);
        Storage::disk('data')->assertExists($adhesion->chemin_png);
        $this->assertStringStartsWith('%PDF', Storage::disk('data')->get($adhesion->chemin_pdf));
        $this->assertSame(2, preg_match_all('/\/Type\s*\/Page[^s]/', Storage::disk('data')->get($adhesion->chemin_pdf)));

        [$largeur, $hauteur] = getimagesize(Storage::disk('data')->path($adhesion->chemin_png));
        $this->assertSame(2100, $largeur);
        $this->assertSame(2400, $hauteur);
    }
}
