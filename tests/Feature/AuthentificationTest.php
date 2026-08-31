<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthentificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('data');
        config(['cotiz.auth.email' => 'bureau@exemple.fr', 'cotiz.auth.password' => 'secret-bureau']);
        $this->seed(UserSeeder::class);
    }

    public function test_sans_authentification_activee_les_ecrans_sont_libres(): void
    {
        config(['cotiz.auth.enabled' => false]);

        $this->get('/adhesions')->assertOk();
        $this->get('/connexion')->assertRedirect('/');
    }

    public function test_avec_authentification_activee_les_ecrans_exigent_une_connexion(): void
    {
        config(['cotiz.auth.enabled' => true]);

        $this->get('/adhesions')->assertRedirect('/connexion');
        $this->get('/connexion')->assertOk()->assertSee('Se connecter');
    }

    public function test_le_compte_seede_depuis_l_environnement_peut_se_connecter(): void
    {
        config(['cotiz.auth.enabled' => true]);

        $this->post('/connexion', ['email' => 'bureau@exemple.fr', 'password' => 'mauvais'])
            ->assertRedirect()->assertSessionHasErrors('email');

        $this->post('/connexion', ['email' => 'bureau@exemple.fr', 'password' => 'secret-bureau'])
            ->assertRedirect('/');

        $this->assertAuthenticated();
        $this->get('/adhesions')->assertOk();

        $this->post('/deconnexion')->assertRedirect('/connexion');
        $this->assertGuest();
    }

    public function test_le_seed_met_a_jour_le_mot_de_passe_sans_dupliquer_le_compte(): void
    {
        config(['cotiz.auth.password' => 'nouveau']);
        $this->seed(UserSeeder::class);

        $this->assertSame(1, User::query()->count());
        $this->assertTrue(auth()->validate(['email' => 'bureau@exemple.fr', 'password' => 'nouveau']));
    }
}
