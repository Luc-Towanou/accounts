
// routes/api.php
    Route::apiResource('mois-comptables', MoisComptableController::class);
    Route::apiResource('tableaux', TableauController::class);
    Route::apiResource('sous-tableaux', SousTableauController::class);
    Route::apiResource('variables', VariableController::class);
    Route::apiResource('operations', OperationController::class);
    Route::apiResource('regles-calcul', RegleCalculController::class);

// 1. Mois comptables

         public function up(): void
    {
        Schema::create('mois_comptables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('mois');
            $table->year('annee');
            $table->string('statut_objet')->default('actif');  
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->decimal('budget_prevu', 12, 2)->nullable();
            $table->decimal('depense_reelle', 12, 2)->default(0);
            $table->decimal('gains_reelle', 12, 2)->default(0);
            $table->decimal('montant_net', 12, 2)->default(0);
            $table->timestamps();
        });
    }

// 2. Tableaux
public function up(): void
    {
        Schema::create('tableaus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mois_comptable_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nom');
            $table->text('description')->nullable();
            $table->enum('nature', ['entree', 'sortie'])->default('sortie');
            $table->string('statut_objet')->default('actif');
            $table->decimal('budget_prevu', 12, 2)->nullable();
            $table->decimal('depense_reelle', 12, 2)->default(0);
            $table->date('date_debut')->nullable();
            $table->date('date_fin')->nullable();
            $table->timestamps();
        });
    }



// 3. Variables
public function up(): void
    {
        Schema::create('variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tableau_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nom');
            $table->enum('type', ['simple', 'sous-tableau'])->default('simple');
            $table->decimal('budget_prevu', 12, 2)->nullable();
            $table->decimal('depense_reelle', 12, 2)->default(0);
            $table->boolean('calcule')->default(false);  
            // $table->text('regle_calcul')->nullable(); //
            $table->string('statut_objet')->default('actif');
            $table->timestamps();
        });
    }
//4. sous_variables
public function up(): void
    {
        Schema::create('sous_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variable_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nom');
            // $table->enum('type', ['simple', 'sous-tableau']);
            $table->decimal('budget_prevu', 12, 2)->nullable();
            $table->decimal('depense_reelle', 12, 2)->default(0);  
            // $table->text('regle_calcul')->nullable(); //
            $table->string('statut_objet')->default('actif');
            $table->boolean('calcule')->default(false);  
            $table->timestamps();
        });
    }

// 5. Operations
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

    }

// 6. Règles de calcul
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
           
       
    }

// 7. categorie
public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique(); // Exemple : Transport, Alimentation, etc.
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('color', 7)->nullable();
            $table->string('slug')->unique();
            $table->string('statut_objet')->default('actif');
            $table->timestamps();
        });
    }
//user_client : lezs clients de lutilisateurs. Ceux avec qui il effectue les differentes operations
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


// Models Laravel avec relations Eloquent

// MoisComptable.php
class MoisComptable extends Model {
    protected $fillable = ['user_id', 'mois', 'annee'];

    public function user() {
        return $this->belongsTo(User::class);
// ...
    }
}

// 3. SousTableaux
Schema::create('sous_tableaux', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tableau_id')->constrained()->onDelete('cascade');
    $table->string('nom');
    $table->decimal('budget_prevu', 12, 2)->nullable();
    $table->decimal('depense_reelle', 12, 2)->default(0);
    $table->timestamps();
});