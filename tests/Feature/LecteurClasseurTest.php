<?php

namespace Tests\Feature;

use App\Exceptions\ClasseurInvalide;
use App\Models\Saison;
use App\Services\LecteurClasseur;
use Database\Seeders\SaisonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\FabriqueClasseur;
use Tests\TestCase;

class LecteurClasseurTest extends TestCase
{
    use RefreshDatabase;

    private LecteurClasseur $lecteur;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('data');
        $this->seed(SaisonSeeder::class);
        $this->lecteur = new LecteurClasseur;
    }

    public function test_le_classeur_modele_rempli_est_lu_ligne_par_ligne(): void
    {
        $chemin = FabriqueClasseur::xlsx(Saison::active(), [
            ['Dupont', 'Marie', 'Marie@Exemple.fr', null, 2, 1, 0, null, '12/09/2025', 'Chèque', 19],
            ['Martin', null, 'x@y.fr', 'z@w.fr', 0, 0, 1, null, null, 'Espèces', '6,50'],
        ]);

        $lignes = $this->lecteur->lire($chemin);

        $this->assertCount(2, $lignes);

        $premiere = $lignes[0];
        $this->assertSame(2, $premiere->numeroLigne);
        $this->assertSame('Dupont', $premiere->nom);
        $this->assertSame('Marie', $premiere->prenom);
        $this->assertSame(['marie@exemple.fr'], $premiere->emails);
        $this->assertSame([2, 1, 0], [$premiere->nbAdultes, $premiere->nbEnfantsFamille, $premiere->nbEnfantsSeuls]);
        $this->assertSame(19.0, $premiere->montantEncaisse);
        $this->assertSame('Chèque', $premiere->modeReglement);
        $this->assertSame('2025-09-12', $premiere->dateReglement->toDateString());

        $seconde = $lignes[1];
        $this->assertNull($seconde->prenom);
        $this->assertSame(['x@y.fr', 'z@w.fr'], $seconde->emails);
        $this->assertSame(6.5, $seconde->montantEncaisse);
        $this->assertNull($seconde->dateReglement);
    }

    public function test_les_lignes_entierement_vides_sont_ignorees(): void
    {
        $chemin = FabriqueClasseur::xlsx(Saison::active(), [
            ['Dupont', 'Marie', 'marie@exemple.fr', null, 1, 0, 0],
            [],
            [],
            ['Durand', 'Paul', 'paul@exemple.fr', null, 1, 0, 0],
        ]);

        $this->assertSame([2, 5], $this->lecteur->lire($chemin)->map->numeroLigne->all());
    }

    public function test_toute_colonne_commencant_par_email_est_lue(): void
    {
        $chemin = FabriqueClasseur::xlsxBrut(
            ['Nom', 'Prenom', 'Email_1', 'Email_2', 'Email_3', 'Nb_adultes', 'Nb_enfants_famille', 'Nb_enfants_seuls', 'Date_reglement', 'Mode_reglement', 'Montant_encaisse'],
            [['Dupont', '', 'a@b.fr', '', 'c@d.fr', 1, 0, 0, '', '', '']],
        );

        $this->assertSame(['a@b.fr', 'c@d.fr'], $this->lecteur->lire($chemin)[0]->emails);
    }

    public function test_un_montant_dans_une_colonne_d_effectif_est_lu_comme_entier(): void
    {
        $chemin = FabriqueClasseur::xlsx(Saison::active(), [
            ['Dupont', 'Marie', 'marie@exemple.fr', null, '2', '1.0', '0', null, null, null, null],
        ]);

        $ligne = $this->lecteur->lire($chemin)[0];

        $this->assertSame([2, 1, 0], [$ligne->nbAdultes, $ligne->nbEnfantsFamille, $ligne->nbEnfantsSeuls]);
    }

    public function test_un_onglet_adhesions_absent_est_refuse(): void
    {
        $chemin = FabriqueClasseur::xlsxBrut(['Nom'], [], 'Feuil1');

        $this->expectException(ClasseurInvalide::class);
        $this->expectExceptionMessage('ADHESIONS');

        $this->lecteur->lire($chemin);
    }

    public function test_des_entetes_incorrects_sont_refuses(): void
    {
        $chemin = FabriqueClasseur::xlsxBrut(['Nom', 'Email_1', 'Adultes'], []);

        $this->expectException(ClasseurInvalide::class);
        $this->expectExceptionMessage('Nb_adultes');

        $this->lecteur->lire($chemin);
    }

    public function test_un_fichier_csv_est_accepte(): void
    {
        $chemin = tempnam(sys_get_temp_dir(), 'cotiz-test-').'.csv';
        file_put_contents($chemin, "Nom,Prenom,Email_1,Nb_adultes,Nb_enfants_famille,Nb_enfants_seuls,Date_reglement,Mode_reglement,Montant_encaisse\nDupont,Marie,marie@exemple.fr,1,0,0,,,8\n");

        $ligne = $this->lecteur->lire($chemin)[0];

        $this->assertSame('Dupont', $ligne->nom);
        $this->assertSame(8.0, $ligne->montantEncaisse);
    }
}
