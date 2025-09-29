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
        Schema::create('regle_calculs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('variable_id')->nullable()->unique()->constrained()->onDelete('cascade');
            $table->foreignId('sous_variable_id')->nullable()->unique()->constrained()->onDelete('cascade');          
            $table->text('expression'); // Exemple : variable:Déplacement + variable:Goûter
            $table->string('statut_objet')->default('actif');
            $table->timestamps();
        });

            // Pour éviter les incohérences
            DB::statement(<<<SQL
            ALTER TABLE regle_calculs
            ADD CONSTRAINT chk_regle_calculs_variable_xor_sous
            CHECK (
                (variable_id IS NOT NULL AND sous_variable_id IS NULL)
                OR
                (variable_id IS NULL AND sous_variable_id IS NOT NULL)
            )
            SQL
                    );
            // $table->check('(variable_id IS NOT NULL AND sous_variable_id IS NULL) OR (variable_id IS NULL AND sous_variable_id IS NOT NULL)');
       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('regle_calculs');
    }
};
