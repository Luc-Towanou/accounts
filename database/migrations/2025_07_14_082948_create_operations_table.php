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
        Schema::create('operations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variable_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('sous_variable_id')->nullable()->constrained()->onDelete('cascade');            
            $table->decimal('montant', 12, 2);
            $table->text('description')->nullable();
            $table->datetime('date')->default(now());
            $table->enum('nature', ['entree', 'sortie'])->default('sortie');
            $table->string('statut_objet')->default('actif');
            $table->timestamps();
        });

            
              // Pour éviter les incohérences
            // check constraint via raw SQL
        DB::statement(<<<SQL
            ALTER TABLE operations
            ADD CONSTRAINT chk_operations_variable_xor_sous
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
        Schema::dropIfExists('operations');
    }
};
