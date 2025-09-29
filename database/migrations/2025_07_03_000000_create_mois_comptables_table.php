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
        Schema::create('mois_comptables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('mois');
            $table->year('annee');
            $table->string('statut_objet')->default('actif');  
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->decimal('budget_prevu', 12, 2)->nullable();
            $table->decimal('depense_reelle', 12, 2)->default(0);
            $table->decimal('gains_reelle', 12, 2)->default(0);
            $table->decimal('montant_net', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mois_comptables');
    }
};
