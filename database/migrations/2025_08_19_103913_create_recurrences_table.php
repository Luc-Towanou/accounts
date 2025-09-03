<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tableau_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('variable_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('sous_variable_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('operation_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('frequence', ['quotidien', 'hebdo', 'mensuel', 'annuel']);
            $table->integer('interval')->default(1); // tous les X jours/semaines/mois
            $table->date('date_debut');
            $table->date('date_fin')->nullable(); // ex : abonnement qui s’arrête après 12 mois
            $table->boolean('auto_apply')->default(true); // true = applique automatiquement, false = nécessite validation utilisateur
            $table->timestamps();
        });

         // Contraintes CHECK pour éviter les incohérences
    DB::statement(<<<SQL
        ALTER TABLE recurrences
        ADD CONSTRAINT chk_recurrences_xor
        CHECK (
            (tableau_id IS NOT NULL AND variable_id IS NULL AND sous_variable_id IS NULL AND operation_id IS NULL)
            OR
            (tableau_id IS NULL AND variable_id IS NOT NULL AND sous_variable_id IS NULL AND operation_id IS NULL)
            OR
            (tableau_id IS NULL AND variable_id IS NULL AND sous_variable_id IS NOT NULL AND operation_id IS NULL)
            OR
            (tableau_id IS NULL AND variable_id IS NULL AND sous_variable_id IS NULL AND operation_id IS NOT NULL)
        )
    SQL
    );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurrences');
    }
};
