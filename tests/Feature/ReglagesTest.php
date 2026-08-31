<?php

namespace Tests\Feature;

use App\Enums\CleReglage;
use App\Livewire\Reglages\Parametres;
use App\Livewire\Reglages\Saisons;
use App\Models\Reglage;
use App\Models\Saison;
use Database\Seeders\ReglageSeeder;
use Database\Seeders\SaisonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ReglagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('data');
        $this->seed([SaisonSeeder::class, ReglageSeeder::class]);
    }

    public function test_la_page_reglages_affiche_la_saison_active(): void
    {
        $this->get('/reglages')->assertOk()->assertSee('2025-2026');
    }

    public function test_les_reglages_sont_enregistres_et_le_mot_de_passe_chiffre(): void
    {
        Livewire::test(Parametres::class)
            ->set('form.expediteur_nom', 'Bureau')
            ->set('form.smtp_password', 'secret-smtp')
            ->call('enregistrer')
            ->assertHasNoErrors()
            ->assertSet('message', 'Réglages enregistrés.');

        $this->assertSame('Bureau', Reglage::valeur(CleReglage::ExpediteurNom));
        $this->assertSame('secret-smtp', Reglage::valeur(CleReglage::SmtpPassword));
        $this->assertNotSame('secret-smtp', Reglage::query()->where('cle', 'smtp_password')->value('valeur'));
    }

    public function test_un_reglage_invalide_est_refuse(): void
    {
        Livewire::test(Parametres::class)
            ->set('form.expediteur_email', 'pas-un-mail')
            ->call('enregistrer')
            ->assertHasErrors(['form.expediteur_email']);
    }

    public function test_un_test_smtp_sur_un_serveur_injoignable_affiche_une_erreur(): void
    {
        Livewire::test(Parametres::class)
            ->set('form.smtp_host', '127.0.0.1')
            ->set('form.smtp_port', '1')
            ->call('testerConnexionSmtp')
            ->assertSet('message', null)
            ->assertSee('Connexion SMTP impossible');
    }

    public function test_les_tarifs_de_la_saison_active_sont_modifiables(): void
    {
        Livewire::test(Saisons::class)
            ->set('form.tarif_adulte', '10')
            ->call('enregistrer')
            ->assertHasNoErrors();

        $this->assertSame('10.00', Saison::active()->tarif_adulte);
    }

    public function test_ouvrir_une_nouvelle_saison_reprend_les_tarifs_et_l_active(): void
    {
        Livewire::test(Saisons::class)
            ->set('nouvelleAnnee', 2026)
            ->call('ouvrirNouvelleSaison')
            ->assertHasNoErrors();

        $nouvelle = Saison::active();

        $this->assertSame('2026-2027', $nouvelle->libelle);
        $this->assertSame('8.00', $nouvelle->tarif_adulte);
        $this->assertSame('2627', $nouvelle->prefixeNumero());
        $this->assertSame(1, Saison::query()->where('active', true)->count());
        Storage::disk('data')->assertExists($nouvelle->visuel_recto);
    }

    public function test_un_libelle_de_saison_deja_utilise_est_refuse(): void
    {
        Livewire::test(Saisons::class)
            ->set('nouvelleAnnee', 2025)
            ->call('ouvrirNouvelleSaison')
            ->assertHasErrors(['nouvelleAnnee']);
    }
}
