<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saisons', function (Blueprint $table) {
            $table->id();
            $table->string('libelle')->unique();
            $table->decimal('tarif_adulte', 8, 2);
            $table->decimal('tarif_enfant_famille', 8, 2);
            $table->decimal('tarif_enfant_seul', 8, 2);
            $table->boolean('active')->default(false);
            $table->string('visuel_recto')->nullable();
            $table->string('visuel_verso')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saisons');
    }
};
