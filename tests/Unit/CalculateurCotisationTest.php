<?php

namespace Tests\Unit;

use App\Models\Saison;
use App\Services\CalculateurCotisation;
use PHPUnit\Framework\TestCase;

class CalculateurCotisationTest extends TestCase
{
    private CalculateurCotisation $calculateur;

    private Saison $saison;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculateur = new CalculateurCotisation;
        $this->saison = new Saison([
            'tarif_adulte' => 8,
            'tarif_enfant_famille' => 3,
            'tarif_enfant_seul' => 6,
        ]);
    }

    public function test_un_adulte_seul_paie_le_tarif_adulte(): void
    {
        $this->assertSame(8.0, $this->calculateur->calculer($this->saison, 1, 0, 0));
    }

    public function test_une_famille_cumule_adultes_et_enfants_en_famille(): void
    {
        $this->assertSame(22.0, $this->calculateur->calculer($this->saison, 2, 2, 0));
    }

    public function test_un_enfant_seul_paie_le_tarif_enfant_seul(): void
    {
        $this->assertSame(6.0, $this->calculateur->calculer($this->saison, 0, 0, 1));
    }

    public function test_des_effectifs_nuls_donnent_une_cotisation_nulle(): void
    {
        $this->assertSame(0.0, $this->calculateur->calculer($this->saison, 0, 0, 0));
    }

    public function test_les_tarifs_decimaux_sont_arrondis_au_centime(): void
    {
        $saison = new Saison([
            'tarif_adulte' => 7.5,
            'tarif_enfant_famille' => 2.25,
            'tarif_enfant_seul' => 5.75,
        ]);

        $this->assertSame(19.5, $this->calculateur->calculer($saison, 2, 2, 0));
        $this->assertSame(5.75, $this->calculateur->calculer($saison, 0, 0, 1));
    }
}
