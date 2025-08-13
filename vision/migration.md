
// routes/api.php
    Route::apiResource('mois-comptables', MoisComptableController::class);
    Route::apiResource('tableaux', TableauController::class);
    Route::apiResource('sous-tableaux', SousTableauController::class);
    Route::apiResource('variables', VariableController::class);
    Route::apiResource('operations', OperationController::class);
    Route::apiResource('regles-calcul', RegleCalculController::class);

// 1. Mois comptables

        Schema::create('mois_comptables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('mois');
            $table->year('annee');
            $table->string('statut_objet')->default('actif');  
            $table->timestamps();
        });

// 2. Tableaux
Schema::create('tableaux', function (Blueprint $table) {
    $table->id();
    $table->foreignId('mois_comptable_id')->constrained()->onDelete('cascade');
    $table->string('nom');
    $table->decimal('budget_prevu', 12, 2)->nullable();
    $table->decimal('depense_reelle', 12, 2)->default(0);
    $table->timestamps();
});

// 3. SousTableaux
Schema::create('sous_tableaux', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tableau_id')->constrained()->onDelete('cascade');
    $table->string('nom');
    $table->decimal('budget_prevu', 12, 2)->nullable();
    $table->decimal('depense_reelle', 12, 2)->default(0);
    $table->timestamps();
});

// 4. Variables
Schema::create('variables', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tableau_id')->nullable()->constrained()->onDelete('cascade');
    $table->foreignId('sous_tableau_id')->nullable()->constrained()->onDelete('cascade');
    $table->string('nom');
    $table->enum('type', ['fixe', 'resultat']);
    $table->decimal('budget_prevu', 12, 2)->nullable();
    $table->decimal('depense_reelle', 12, 2)->default(0);
    $table->timestamps();
});

// 5. Operations
Schema::create('operations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('variable_id')->constrained()->onDelete('cascade');
    $table->decimal('montant', 12, 2);
    $table->text('description')->nullable();
    $table->timestamp('date_operation')->useCurrent();
    $table->timestamps();
});

// 6. Règles de calcul
Schema::create('regles_calcul', function (Blueprint $table) {
    $table->id();
    $table->foreignId('variable_id')->constrained()->onDelete('cascade');
    $table->text('expression'); // par exemple : sous_tableau1 + sous_tableau2 - variableX
    $table->timestamps();
});

// Models Laravel avec relations Eloquent

// MoisComptable.php
class MoisComptable extends Model {
    protected $fillable = ['user_id', 'mois', 'annee'];

    public function user() {
        return $this->belongsTo(User::class);
// ...
    }
}