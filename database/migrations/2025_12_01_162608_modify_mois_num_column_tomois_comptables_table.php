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
        //
        
        // Schema::table('mois_comptables', function (Blueprint $table) {
        //         $table->dropColumn('mois_num'); // on commence nullable
        //     });

            Schema::table('mois_comptables', function (Blueprint $table) {
                $table->integer('mois_num')->nullable(); // on commence nullable
            });

            // Mettre à jour les données existantes
            DB::table('mois_comptables')->get()->each(function ($row) {
                $map = [
                    'janvier'   => 1,
                    'février'   => 2,
                    'mars'      => 3,
                    'avril'     => 4,
                    'mai'       => 5,
                    'juin'      => 6,
                    'juillet'   => 7,
                    'aout'      => 8,
                    'septembre' => 9,
                    'octobre'   => 10,
                    'novembre'  => 11,
                    'décembre'  => 12,
                    'decembre'  => 12,
                ];

                $moisNum = $map[strtolower($row->mois)] ?? null;

                DB::table('mois_comptables')
                    ->where('id', $row->id)
                    ->update(['mois_num' => $moisNum]);
            });

            // Rendre la colonne non nulle après mise à jour
            Schema::table('mois_comptables', function (Blueprint $table) {
                $table->integer('mois_num')->nullable(false)->change();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
