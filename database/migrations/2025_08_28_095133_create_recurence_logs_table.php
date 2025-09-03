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
        Schema::create('recurence_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurrence_id')->constrained()->onDelete('cascade');
            $table->date('date_execution'); // la date prévue de l’occurrence
            $table->boolean('appliquee')->default(false); // si l’occurrence a été vraiment appliquée        
            $table->timestamps();
            
            // Empêcher les doublons pour une même récurrence/date
            $table->unique(['recurrence_id', 'date_execution']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurence_logs');
    }
};
