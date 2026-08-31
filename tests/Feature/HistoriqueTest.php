<?php

namespace Tests\Feature;

use App\Enums\StatutAdhesion;
use App\Models\Saison;
use Database\Seeders\ReglageSeeder;
use Database\Seeders\SaisonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HistoriqueTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('data');
        $this->seed([SaisonSeeder::class, ReglageSeeder::class]);
    }

    public function test_l_historique_liste_les_saisons_avec_leurs_compteurs(): void
    {
        $this->adhesion('Dupont')->update(['statut' => StatutAdhesion::Envoye]);
        $this->adhesion('Martin');

        $this->get('/historique')->assertOk()->assertSee('2025-2026')->assertSeeInOrder(['<td>2</td>', '<td>1</td>'], false);
        $this->get('/historique/'.Saison::active()->id)->assertOk()->assertSee('DUPONT Marie')->assertSee('MARTIN Marie');
    }

    private function adhesion(string $nom)
    {
        $saison = Saison::active();
        $adhesion = $saison->adhesions()->create([
            'numero' => $saison->prochainNumero(), 'nom' => $nom, 'prenom' => 'Marie',
            'nb_adultes' => 2, 'nb_enfants_famille' => 1, 'nb_enfants_seuls' => 0, 'cotisation_calculee' => 19,
        ]);
        $adhesion->destinataires()->create(['email' => 'marie@exemple.fr']);

        return $adhesion->refresh();
    }
}
