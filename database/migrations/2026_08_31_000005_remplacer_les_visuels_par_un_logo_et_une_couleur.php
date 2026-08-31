<?php

use App\Models\Saison;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('saisons', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('active');
            $table->string('couleur', 7)->default(Saison::COULEUR_PAR_DEFAUT)->after('logo');
            $table->dropColumn(['visuel_recto', 'visuel_verso']);
        });

        foreach (Saison::query()->whereNull('logo')->get() as $saison) {
            $saison->update(['logo' => $this->copierLogoInitial($saison)]);
        }
    }

    public function down(): void
    {
        Schema::table('saisons', function (Blueprint $table) {
            $table->string('visuel_recto')->nullable();
            $table->string('visuel_verso')->nullable();
            $table->dropColumn(['logo', 'couleur']);
        });
    }

    private function copierLogoInitial(Saison $saison): string
    {
        $chemin = "visuels/saison-{$saison->id}-logo.png";

        Storage::disk('data')->put($chemin, file_get_contents(database_path('seeders/visuels/logo.png')));

        return $chemin;
    }
};
