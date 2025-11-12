<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('operations', function (Blueprint $table) {
            // Suppression des colonnes existantes
            $table->dropForeign(['variable_id']);
            $table->dropColumn('variable_id');

            $table->dropForeign(['sous_variable_id']);
            $table->dropColumn('sous_variable_id');

            // Ajout de la nouvelle colonne categorie_id
            $table->foreignId('categorie_id')
                  ->nullable()
                  ->constrained('categories')
                  ->onDelete('cascade');

            // Ajout user_id
            $table->unsignedBigInteger('user_id')->nullable(); 
            $table->foreign('user_id')
                  ->references('id')->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('operations', function (Blueprint $table) {
            // Suppression de la colonne categorie_id
            $table->dropForeign(['categorie_id']);
            $table->dropColumn('categorie_id');

            // Suppression de la colonne user_id
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            // Réajout des colonnes supprimées
            $table->foreignId('variable_id')
                  ->nullable()
                  ->constrained()
                  ->onDelete('cascade');

            $table->foreignId('sous_variable_id')
                  ->nullable()
                  ->constrained()
                  ->onDelete('cascade');
        });
    }
};
