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
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // l'utilisateur concerné

            // Relation dynamique selon le type
            $table->unsignedBigInteger('variable_id')->nullable();
            $table->unsignedBigInteger('sous_variable_id')->nullable();
            $table->unsignedBigInteger('tableau_id')->nullable();
            $table->string('title'); 
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('type', ['variable', 'sous_variable', 'tableau']); 
            $table->enum('periode', ['jour', 'mois', 'annee']); 

            $table->decimal('target_amount', 12, 2); // objectif fixé
            $table->decimal('progress', 12, 2)->default(0); // progression
            $table->enum('status', ['en_cours', 'atteint'])->default('en_cours');

            $table->timestamps();

            // Clés étrangères
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('variable_id')->references('id')->on('variables')->onDelete('set null');
            $table->foreign('sous_variable_id')->references('id')->on('sous_variables')->onDelete('set null');
            $table->foreign('tableau_id')->references('id')->on('tableaus')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
        
    }
};
