<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('cotiz.auth.email');
        $motDePasse = config('cotiz.auth.password');

        if (empty($email) || empty($motDePasse)) {
            return;
        }

        $utilisateur = User::query()->firstOrNew(['email' => $email]);

        if (! $utilisateur->exists || ! Hash::check($motDePasse, $utilisateur->password)) {
            $utilisateur->fill(['name' => 'Bureau', 'password' => Hash::make($motDePasse)])->save();
        }
    }
}
