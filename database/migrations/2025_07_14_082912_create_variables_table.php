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
        Schema::create('variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tableau_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('categorie_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->string('nom');
            $table->enum('type', ['simple', 'sous-tableau']);
            $table->decimal('budget_prevu', 12, 2)->nullable();
            $table->decimal('depense_reelle', 12, 2)->default(0);
            $table->boolean('calcule')->default(false);  
            // $table->text('regle_calcul')->nullable(); //
            $table->string('statut_objet')->default('actif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variables');
    }
};
