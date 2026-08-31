<?php

namespace Tests\Feature;

use App\Dto\LigneAdhesion;
use App\Enums\NiveauVerdict;
use App\Models\Saison;
use App\Services\ValidateurImport;
use Database\Seeders\SaisonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ValidateurImportTest extends TestCase
{
    use RefreshDatabase;

    private ValidateurImport $validateur;

    private Saison $saison;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('data');
        $this->seed(SaisonSeeder::class);
        $this->validateur = app(ValidateurImport::class);
        $this->saison = Saison::active();
    }

    public function test_une_ligne_complete_et_coherente_est_valide(): void
    {
        $verdict = $this->validateur->valider($this->ligne(montant: 19.0), $this->saison);

        $this->assertSame(NiveauVerdict::Valide, $verdict->niveau);
        $this->assertSame(19.0, $verdict->cotisationCalculee);
    }

    public function test_un_nom_vide_est_rejete(): void
    {
        $verdict = $this->validateur->valider($this->ligne(nom: ''), $this->saison);

        $this->assertSame(NiveauVerdict::Rejet, $verdict->niveau);
        $this->assertContains('Le nom est vide.', $verdict->motifs);
    }

    public function test_une_ligne_sans_email_valide_est_rejetee(): void
    {
        $verdict = $this->validateur->valider($this->ligne(emails: ['pas-un-mail']), $this->saison);

        $this->assertSame(NiveauVerdict::Rejet, $verdict->niveau);
    }

    public function test_des_effectifs_tous_nuls_sont_rejetes(): void
    {
        $verdict = $this->validateur->valider($this->ligne(adultes: 0, enfantsFamille: 0), $this->saison);

        $this->assertSame(NiveauVerdict::Rejet, $verdict->niveau);
    }

    public function test_un_montant_encaisse_different_donne_un_avertissement(): void
    {
        $verdict = $this->validateur->valider($this->ligne(montant: 20.0), $this->saison);

        $this->assertSame(NiveauVerdict::Avertissement, $verdict->niveau);
        $this->assertStringContainsString('20 €', $verdict->motifs[0]);
        $this->assertStringContainsString('19 €', $verdict->motifs[0]);
    }

    public function test_un_homonyme_dans_la_saison_donne_un_avertissement(): void
    {
        $this->saison->adhesions()->create([
            'numero' => '2526-001', 'nom' => 'DUPONT', 'prenom' => 'marie',
            'nb_adultes' => 1, 'cotisation_calculee' => 8,
        ]);

        $verdict = $this->validateur->valider($this->ligne(), $this->saison);

        $this->assertSame(NiveauVerdict::Avertissement, $verdict->niveau);
        $this->assertContains('Un homonyme existe déjà dans la saison.', $verdict->motifs);
    }

    private function ligne(string $nom = 'Dupont', array $emails = ['marie@exemple.fr'], int $adultes = 2, int $enfantsFamille = 1, ?float $montant = null): LigneAdhesion
    {
        return new LigneAdhesion(
            numeroLigne: 2, nom: $nom, prenom: 'Marie', emails: $emails,
            nbAdultes: $adultes, nbEnfantsFamille: $enfantsFamille, nbEnfantsSeuls: 0,
            montantEncaisse: $montant, modeReglement: null, dateReglement: null,
        );
    }
}
