<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Supprime l’ancienne table si elle existe (ancienne version)
     */
    public function up(): void
    {
        Schema::table('variables', function (Blueprint $table) {
            $table->dropForeign(['categorie_id']);
        });

        Schema::table('sous_variables', function (Blueprint $table) {
            $table->dropForeign(['categorie_id']);
        });
        Schema::dropIfExists('categories');

        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            // 🧭 Lien vers le mois comptable (chaque catégorie appartient à un mois)
            $table->foreignId('mois_comptable_id')
                  ->nullable()
                  ->constrained('mois_comptables')
                  ->onDelete('cascade');

            // 👤 Lien vers l’utilisateur propriétaire (nullable si template global)
            $table->foreignId('user_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('cascade');

            // 🧩 Gestion hiérarchique : permet d’avoir des sous-catégories
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('categories')
                  ->onDelete('cascade');

            // 🏷 Informations de base
            $table->string('nom');
            // $table->enum('type', ['tableau', 'variable', 'sous-variable'])
            //       ->default('tableau');
            $table->integer('niveau');
            $table->text('description')->nullable();
            $table->enum('nature', ['entree', 'sortie'])->default('sortie');
            // $table->boolean('calcule')->default(false);  

            // statut d'objet 
            $table->string('statut_objet')->default('actif');

            // 💰 Données budgétaires
            $table->decimal('budget_prevu', 15, 2)->nullable();
            $table->decimal('depense_reelle', 15, 2)->default(0);
            // $table->decimal('gains_reelle', 15, 2)->default(0);
            // $table->decimal('montant_net', 15, 2)->default(0);

            // ⚙ Gestion des calculs
            $table->boolean('calcule')->default(false); // si la valeur est déterminée automatiquement
            // $table->text('regle_calcul')->nullable();   // expression de la règle de calcul (JSON, texte, etc.)

            // 📘 Template & visibilité
            $table->boolean('is_template')->default(false); // true = modèle précréé accessible à tous
            $table->enum('visibilite', ['public', 'prive'])
                  ->default('prive'); // public = visible de tous, privé = visible du user seulement
            
            //date
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            
            // 🕒 Métadonnées
            $table->timestamps();
        });
    }

    /**
     * Rollback = suppression propre
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};