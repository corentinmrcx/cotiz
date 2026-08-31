<?php

namespace Tests\Feature;

use App\Livewire\Adhesions\Formulaire;
use App\Livewire\Import\Import;
use App\Models\Adhesion;
use App\Models\Saison;
use Database\Seeders\ReglageSeeder;
use Database\Seeders\SaisonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Support\FabriqueClasseur;
use Tests\TestCase;

class ImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('data');
        $this->seed([SaisonSeeder::class, ReglageSeeder::class]);
    }

    public function test_le_classeur_modele_se_telecharge(): void
    {
        $this->get('/import/classeur-modele')
            ->assertOk()
            ->assertDownload('CoTiz_Classeur_Adhesions_MODELE_2025-2026.xlsx');
    }

    public function test_l_analyse_repartit_les_lignes_en_trois_groupes_sans_rien_ecrire(): void
    {
        $composant = Livewire::test(Import::class)
            ->set('fichier', $this->classeur())
            ->call('analyser')
            ->assertHasNoErrors()
            ->assertSet('analyse', true);

        $this->assertCount(1, $composant->get('groupes.valide'));
        $this->assertCount(1, $composant->get('groupes.avertissement'));
        $this->assertCount(1, $composant->get('groupes.rejet'));
        $this->assertSame(0, Adhesion::query()->count());
    }

    public function test_l_import_enregistre_les_lignes_valides_et_numerote(): void
    {
        Livewire::test(Import::class)
            ->set('fichier', $this->classeur())
            ->call('analyser')
            ->call('importer')
            ->assertSet('message', '1 adhésion(s) importée(s).');

        $adhesion = Adhesion::query()->sole();
        $this->assertSame('2526-001', $adhesion->numero);
        $this->assertSame('19.00', $adhesion->cotisation_calculee);
        $this->assertSame(['marie@exemple.fr'], $adhesion->emailsDestinataires());
    }

    public function test_les_avertissements_sont_importes_apres_confirmation(): void
    {
        Livewire::test(Import::class)
            ->set('fichier', $this->classeur())
            ->call('analyser')
            ->set('importerAvertissements', true)
            ->call('importer')
            ->assertSet('message', '2 adhésion(s) importée(s).');

        $this->assertSame(['2526-001', '2526-002'], Adhesion::query()->orderBy('numero')->pluck('numero')->all());
    }

    public function test_un_classeur_sans_onglet_adhesions_affiche_une_erreur(): void
    {
        $chemin = FabriqueClasseur::xlsxBrut(['Nom'], [], 'Feuil1');

        Livewire::test(Import::class)
            ->set('fichier', UploadedFile::fake()->createWithContent('classeur.xlsx', file_get_contents($chemin)))
            ->call('analyser')
            ->assertSet('analyse', false)
            ->assertSee('ADHESIONS');
    }

    public function test_la_saisie_manuelle_cree_une_adhesion_avec_cotisation_calculee(): void
    {
        Livewire::test(Formulaire::class)
            ->set('form.nom', 'Durand')
            ->set('form.prenom', 'Paul')
            ->set('form.emails', 'paul@exemple.fr, PAUL2@exemple.fr')
            ->set('form.nb_adultes', 1)
            ->set('form.nb_enfants_seuls', 1)
            ->call('enregistrer')
            ->assertHasNoErrors()
            ->assertRedirect('/adhesions');

        $adhesion = Adhesion::query()->sole();
        $this->assertSame('14.00', $adhesion->cotisation_calculee);
        $this->assertSame(['paul@exemple.fr', 'paul2@exemple.fr'], $adhesion->emailsDestinataires());
    }

    public function test_la_saisie_manuelle_refuse_une_adhesion_sans_effectif(): void
    {
        Livewire::test(Formulaire::class)
            ->set('form.nom', 'Durand')
            ->set('form.emails', 'paul@exemple.fr')
            ->call('enregistrer')
            ->assertHasErrors(['form.emails']);

        $this->assertSame(0, Adhesion::query()->count());
    }

    public function test_la_modification_recalcule_la_cotisation_et_remplace_les_destinataires(): void
    {
        $adhesion = Saison::active()->adhesions()->create([
            'numero' => '2526-001', 'nom' => 'Durand', 'nb_adultes' => 1, 'cotisation_calculee' => 8,
        ]);
        $adhesion->destinataires()->create(['email' => 'ancien@exemple.fr']);
        $adhesion->refresh();

        Livewire::test(Formulaire::class, ['adhesion' => $adhesion])
            ->assertSet('form.nom', 'Durand')
            ->set('form.emails', 'nouveau@exemple.fr')
            ->set('form.nb_adultes', 2)
            ->call('enregistrer')
            ->assertHasNoErrors();

        $adhesion->refresh();
        $this->assertSame('16.00', $adhesion->cotisation_calculee);
        $this->assertSame(['nouveau@exemple.fr'], $adhesion->emailsDestinataires());
        $this->assertSame('2526-001', $adhesion->numero);
    }

    private function classeur(): UploadedFile
    {
        $chemin = FabriqueClasseur::xlsx(Saison::active(), [
            ['Dupont', 'Marie', 'marie@exemple.fr', null, 2, 1, 0, null, null, 'Chèque', 19],
            ['Martin', 'Luc', 'luc@exemple.fr', null, 1, 0, 0, null, null, 'Espèces', 10],
            ['', '', 'sans-nom@exemple.fr', null, 1, 0, 0],
        ]);

        return UploadedFile::fake()->createWithContent('classeur.xlsx', file_get_contents($chemin));
    }
}
