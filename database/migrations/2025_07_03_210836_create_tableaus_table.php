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
        Schema::create('tableaus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mois_comptable_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nom');
            $table->text('description')->nullable();
            $table->enum('nature', ['entree', 'sortie'])->default('sortie');
            $table->string('statut_objet')->default('actif');
            $table->decimal('budget_prevu', 12, 2)->nullable();
            $table->decimal('depense_reelle', 12, 2)->default(0);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tableaus');
    }
};
