<?php

namespace Tests\Feature;

use App\Enums\CleReglage;
use App\Enums\StatutAdhesion;
use App\Livewire\Adhesions\Liste;
use App\Mail\CarteAdherentMail;
use App\Models\Adhesion;
use App\Models\Reglage;
use App\Models\Saison;
use App\Services\ComposeurMail;
use App\Services\EnvoyeurCarte;
use Database\Seeders\ReglageSeeder;
use Database\Seeders\SaisonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class EnvoiCarteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('data');
        $this->seed([SaisonSeeder::class, ReglageSeeder::class]);
        Mail::fake();
    }

    public function test_le_composeur_remplace_les_variables_de_l_objet_et_du_corps(): void
    {
        $adhesion = $this->adhesion();
        Reglage::definir(CleReglage::MailCorps, 'Bonjour {{prenom}} {{ nom }}, {{cotisation}} € pour {{nb_adultes}} adulte(s) et {{nb_enfants}} enfant(s), saison {{saison}}. {{asso_nom}} {{inconnue}}');

        $composeur = app(ComposeurMail::class);

        $this->assertSame('Votre carte d\'adhérent 2025-2026 — Foyer de Soudron', $composeur->objet($adhesion));
        $this->assertSame('Bonjour Marie DUPONT, 19 € pour 2 adulte(s) et 1 enfant(s), saison 2025-2026. Foyer de Soudron {{inconnue}}', $composeur->corps($adhesion));
    }

    public function test_l_envoi_joint_le_pdf_integre_le_png_et_met_le_statut_a_envoye(): void
    {
        $adhesion = $this->adhesion();

        app(EnvoyeurCarte::class)->envoyer($adhesion);

        $adhesion->refresh();
        $this->assertSame(StatutAdhesion::Envoye, $adhesion->statut);
        $this->assertNotNull($adhesion->date_envoi);
        Storage::disk('data')->assertExists($adhesion->chemin_pdf);

        Mail::assertSent(CarteAdherentMail::class, function (CarteAdherentMail $mail) use ($adhesion) {
            $mail->assertTo('marie@exemple.fr')->assertTo('pierre@exemple.fr')->assertHasBcc('contact@exemple.org');
            $mail->assertHasAttachment(Storage::disk('data')->path($adhesion->chemin_pdf), ['as' => basename($adhesion->chemin_pdf), 'mime' => 'application/pdf']);
            $mail->assertSeeInHtml('Nous vous remercions')->assertSeeInHtml('<img', escape: false);

            return $mail->objet === 'Votre carte d\'adhérent 2025-2026 — Foyer de Soudron';
        });
    }

    public function test_la_copie_cachee_est_absente_si_le_reglage_est_inactif(): void
    {
        Reglage::definir(CleReglage::CopieCacheeActive, '0');

        app(EnvoyeurCarte::class)->envoyer($this->adhesion());

        Mail::assertSent(CarteAdherentMail::class, fn (CarteAdherentMail $mail) => ! $mail->hasBcc('contact@exemple.org'));
    }

    public function test_un_echec_d_envoi_est_enregistre_sans_interrompre(): void
    {
        Mail::shouldReceive('purge');
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP injoignable'));
        $adhesion = $this->adhesion();

        app(EnvoyeurCarte::class)->envoyer($adhesion);

        $adhesion->refresh();
        $this->assertSame(StatutAdhesion::Echec, $adhesion->statut);
        $this->assertSame('SMTP injoignable', $adhesion->erreur_envoi);
    }

    public function test_la_generation_en_test_produit_les_cartes_sans_envoyer(): void
    {
        $this->adhesion();
        $this->adhesion('Martin');

        $composant = Livewire::test(Liste::class)->call('demarrerGenerationTest')->assertSet('nbTotal', 2);
        $composant->call('traiterSuivante')->call('traiterSuivante')->assertSet('traitement', null);

        Mail::assertNothingSent();
        $this->assertSame(2, Adhesion::query()->whereNotNull('chemin_pdf')->count());
        $this->assertSame(0, Adhesion::query()->where('statut', StatutAdhesion::Envoye->value)->count());
    }

    public function test_l_envoi_groupe_traite_une_adhesion_par_requete_et_ignore_les_envoyees(): void
    {
        $this->adhesion();
        $this->adhesion('Martin')->update(['statut' => StatutAdhesion::Envoye]);
        $this->adhesion('Durand')->update(['statut' => StatutAdhesion::Echec]);

        $composant = Livewire::test(Liste::class)->call('demarrerEnvoi')->assertSet('nbTotal', 2);
        $composant->call('traiterSuivante');
        Mail::assertSentCount(1);
        $composant->call('traiterSuivante')->assertSet('traitement', null);

        Mail::assertSentCount(2);
        $this->assertSame(3, Adhesion::query()->where('statut', StatutAdhesion::Envoye->value)->count());
    }

    public function test_le_renvoi_unitaire_envoie_une_seule_carte(): void
    {
        $adhesion = $this->adhesion();

        Livewire::test(Liste::class)->call('renvoyer', $adhesion->id)->assertSet('message', 'Carte 2526-001 envoyée.');

        Mail::assertSentCount(1);
    }

    private function adhesion(string $nom = 'Dupont'): Adhesion
    {
        $saison = Saison::active();
        $adhesion = $saison->adhesions()->create([
            'numero' => $saison->prochainNumero(), 'nom' => $nom, 'prenom' => 'Marie',
            'nb_adultes' => 2, 'nb_enfants_famille' => 1, 'nb_enfants_seuls' => 0, 'cotisation_calculee' => 19,
        ]);
        $adhesion->destinataires()->createMany([['email' => 'marie@exemple.fr'], ['email' => 'pierre@exemple.fr']]);

        return $adhesion->refresh();
    }
}
