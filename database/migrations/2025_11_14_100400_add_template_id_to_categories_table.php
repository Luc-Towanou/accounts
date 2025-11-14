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
        Schema::table('categories', function (Blueprint $table) {
            // Ajout de la colonne template_id
            $table->unsignedBigInteger('template_id')->nullable()->after('id');

            // Clé étrangère auto-référencée (une catégorie peut pointer vers son modèle)
            $table->foreign('template_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('set null');
        });
    }

    
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            // Suppression de la contrainte et de la colonne
            $table->dropForeign(['template_id']);
            $table->dropColumn('template_id');
        });
    }
};
