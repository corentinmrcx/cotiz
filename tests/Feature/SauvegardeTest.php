<?php

namespace Tests\Feature;

use App\Enums\CleReglage;
use App\Exceptions\SauvegardeInvalide;
use App\Models\Adhesion;
use App\Models\Reglage;
use App\Models\Saison;
use App\Services\EtatSauvegarde;
use App\Services\SauvegardeApplication;
use Database\Seeders\ReglageSeeder;
use Database\Seeders\SaisonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class SauvegardeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('data');
        $this->seed([SaisonSeeder::class, ReglageSeeder::class]);
    }

    public function test_le_bandeau_signale_les_modifications_non_exportees(): void
    {
        $etat = app(EtatSauvegarde::class);

        $this->assertFalse($etat->modificationsNonExportees());
        $this->get('/adhesions')->assertDontSee('Certaines données ne sont pas exportées');

        $adhesion = $this->adhesion();
        $this->assertTrue($etat->modificationsNonExportees());
        $this->get('/adhesions')->assertSee('Certaines données ne sont pas exportées');

        app(SauvegardeApplication::class)->exporter();
        $this->assertFalse($etat->modificationsNonExportees());

        $adhesion->update(['nom' => 'Durand']);
        $this->assertTrue($etat->modificationsNonExportees());

        app(SauvegardeApplication::class)->exporter();
        $adhesion->delete();
        $this->assertTrue($etat->modificationsNonExportees());
    }

    public function test_l_export_puis_la_restauration_reconstituent_les_donnees_et_les_fichiers(): void
    {
        $adhesion = $this->adhesion();
        Storage::disk('data')->put('cartes/2025-2026/carte.pdf', 'contenu-pdf');
        Reglage::definir(CleReglage::SmtpPassword, 'secret-smtp');

        $archive = app(SauvegardeApplication::class)->exporter();

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($archive));
        $this->assertNotFalse($zip->getFromName('cotiz-sauvegarde.json'));
        $this->assertNotFalse($zip->getFromName('cartes/2025-2026/carte.pdf'));
        $this->assertStringContainsString('"secret-smtp"', $zip->getFromName('cotiz-sauvegarde.json'));
        $zip->close();

        $adhesion->delete();
        Saison::active()->update(['tarif_adulte' => 99]);
        Reglage::definir(CleReglage::SmtpPassword, 'autre');
        Storage::disk('data')->deleteDirectory('cartes');

        app(SauvegardeApplication::class)->restaurer($archive);

        $this->assertSame('2526-001', Adhesion::query()->sole()->numero);
        $this->assertSame(['marie@exemple.fr'], Adhesion::query()->sole()->emailsDestinataires());
        $this->assertSame('8.00', Saison::active()->tarif_adulte);
        $this->assertSame('secret-smtp', Reglage::valeur(CleReglage::SmtpPassword));
        $this->assertNotSame('secret-smtp', Reglage::query()->where('cle', 'smtp_password')->value('valeur'));
        $this->assertSame('contenu-pdf', Storage::disk('data')->get('cartes/2025-2026/carte.pdf'));
        $this->assertFalse(app(EtatSauvegarde::class)->modificationsNonExportees());

        unlink($archive);
    }

    public function test_une_archive_qui_n_est_pas_une_sauvegarde_est_refusee_sans_rien_toucher(): void
    {
        $this->adhesion();

        $chemin = tempnam(sys_get_temp_dir(), 'cotiz-test-').'.zip';
        $zip = new ZipArchive;
        $zip->open($chemin, ZipArchive::CREATE);
        $zip->addFromString('autre.txt', 'rien');
        $zip->close();

        $this->expectException(SauvegardeInvalide::class);

        try {
            app(SauvegardeApplication::class)->restaurer($chemin);
        } finally {
            $this->assertSame(1, Adhesion::query()->count());
            unlink($chemin);
        }
    }

    public function test_la_sauvegarde_se_telecharge(): void
    {
        $this->get('/sauvegarde')
            ->assertOk()
            ->assertDownload('CoTiz_Sauvegarde_'.now()->format('Y-m-d').'.zip');
    }

    private function adhesion(): Adhesion
    {
        $saison = Saison::active();
        $adhesion = $saison->adhesions()->create([
            'numero' => '2526-001', 'nom' => 'Dupont', 'prenom' => 'Marie',
            'nb_adultes' => 1, 'cotisation_calculee' => 8,
        ]);
        $adhesion->destinataires()->create(['email' => 'marie@exemple.fr']);

        return $adhesion->refresh();
    }
}
