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
        Schema::create('user_clients', function (Blueprint $table) {
            $table->id();
             $table->unsignedBigInteger('user_id'); 

            $table->enum('type', ['beneficiaire', 'payeur']);
            // Type du client : "beneficiaire" (celui qui reçoit un paiement)
            // ou "payeur" (celui qui effectue un paiement)

            $table->string('nom');
            // Nom du client (personne ou entité)

            $table->string('lieu')->nullable();
            // Lieu ou adresse (optionnel, peut être vide)

            $table->string('email')->nullable();
            // Email du client (facultatif, utile pour des envois ou suivi)

            $table->string('telephone')->nullable();
            // Téléphone du client (facultatif, pour contact direct)

            $table->timestamps();
            // Colonnes created_at et updated_at automatiques

            // ---- Contraintes ----
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
            // Si l’utilisateur est supprimé, tous ses clients le sont aussi
    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_clients');
    }
};
