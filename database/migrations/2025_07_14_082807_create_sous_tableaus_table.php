<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sous_tableaus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tableau_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nom');
            $table->decimal('budget_prevu', 12, 2)->nullable();
            $table->decimal('depense_reelle', 12, 2)->default(0);
            $table->boolean('calcule')->default(false);
            $table->string('statut_objet')->default('actif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sous_tableaus');
    }
};
