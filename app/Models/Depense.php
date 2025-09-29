<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Depense extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'goal_id',          // L'objectif lié à cette dépense
        'variable_id',      // Si la dépense est liée à une variable
        'sous_variable_id',  // Si la dépense est liée à une sous-variable
        'tableau_id',       // Si la dépense est liée à un tableau
        'depense_reelle',           // Montant de la dépense relle
        'date',             // Date de la dépense

    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec l'objectif
     */
    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }

    /**
     * Relation avec la variable
     */
    public function variable()
    {
        return $this->belongsTo(Variable::class);
    }

    /**
     * Relation avec la sous-variable
     */
    public function sousVariable()
    {
        return $this->belongsTo(SousVariable::class);
    }

    /**
     * Relation avec le tableau
     */
    public function tableau()
    {
        return $this->belongsTo(Tableau::class);
    }
}


        