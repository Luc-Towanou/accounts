<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Variable extends Model
{
    /** @use HasFactory<\Database\Factories\VariableFactory> */
    use HasFactory;

    protected $fillable = [
    'user_id',
    'tableau_id',
    // 'sous_tableau_id',
    'nom',
    'type', // 'fixe' ou 'resultat'
    'budget_prevu',
    'depense_reelle',
    // 'regle_calcul',
    'statut_objet',
    'calcule',
    'categorie_id'
    ];

    public function tableau()
    {
        return $this->belongsTo(Tableau::class);
    }

    public function sousVariables()
    {
        return $this->hasMany(SousVariable::class);
    }

    public function operations()
    {
        return $this->hasMany(Operation::class);
    }


    public function isSousTableau()
    {
        return $this->type === 'sous-tableau';
    }

    public function regleCalcul()
    {
        return $this->hasOne(RegleCalcul::class);
    }

    public function getDepenseReelleAttribute()
    {
        return $this->operations->sum('montant');
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class);
    }

     public function user()
    {
        return $this->belongsTo(User::class);
    }

}
