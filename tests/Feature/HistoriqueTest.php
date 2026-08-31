<?php

namespace Tests\Feature;

use App\Enums\StatutAdhesion;
use App\Models\Saison;
use App\Services\ExportateurSaison;
use App\Services\GenerateurCarte;
use Database\Seeders\ReglageSeeder;
use Database\Seeders\SaisonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

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

    public function test_l_export_zip_contient_le_csv_et_les_pdf(): void
    {
        $avecCarte = $this->adhesion('Dupont');
        app(GenerateurCarte::class)->generer($avecCarte);
        $this->adhesion('Martin');

        $chemin = app(ExportateurSaison::class)->exporter(Saison::active());

        $archive = new ZipArchive;
        $this->assertTrue($archive->open($chemin));
        $this->assertSame(2, $archive->numFiles);

        $csv = $archive->getFromName('adhesions_2025-2026.csv');
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Numero;Nom;Prenom;Emails', $csv);
        $this->assertStringContainsString('2526-001;DUPONT;Marie;marie@exemple.fr;2;1;0;19,00', $csv);
        $this->assertStringContainsString('2526-002;MARTIN', $csv);
        $this->assertNotFalse($archive->getFromName('cartes/CarteAdherent_FoyerSoudron_2025-2026_DUPONT-Marie.pdf'));
        $archive->close();

        $this->get('/historique/'.Saison::active()->id.'/export')->assertOk()->assertDownload('CoTiz_2025-2026.zip');
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
