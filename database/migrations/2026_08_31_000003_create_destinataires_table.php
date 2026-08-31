<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinataires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adhesion_id')->constrained('adhesions')->cascadeOnDelete();
            $table->string('email');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinataires');
    }
};
