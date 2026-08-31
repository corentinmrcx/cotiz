<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adhesions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('saison_id')->constrained('saisons')->cascadeOnDelete();
            $table->string('numero')->unique();
            $table->string('nom');
            $table->string('prenom')->nullable();
            $table->unsignedInteger('nb_adultes')->default(0);
            $table->unsignedInteger('nb_enfants_famille')->default(0);
            $table->unsignedInteger('nb_enfants_seuls')->default(0);
            $table->decimal('cotisation_calculee', 8, 2);
            $table->decimal('montant_encaisse', 8, 2)->nullable();
            $table->string('mode_reglement')->nullable();
            $table->date('date_reglement')->nullable();
            $table->string('statut')->default('a_envoyer');
            $table->dateTime('date_envoi')->nullable();
            $table->text('erreur_envoi')->nullable();
            $table->string('chemin_pdf')->nullable();
            $table->string('chemin_png')->nullable();
            $table->timestamps();

            $table->index(['saison_id', 'statut']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adhesions');
    }
};
