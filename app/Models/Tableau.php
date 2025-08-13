<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tableau extends Model
{
    /** @use HasFactory<\Database\Factories\TableauFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mois_comptable_id', 
        'nom', 
        'budget_prevu', 
        'statut_objet', 
        'date_debut', 
        'date_fin', 
        'description',
        'depense_reelle',
        'nature',
    ];

public function moisComptable()
{
    return $this->belongsTo(MoisComptable::class, 'mois_comptable_id');
}

// public function sousTableaux()
// {
//     return $this->hasMany(SousTableau::class);
// }

public function variables()
{
    return $this->hasMany(Variable::class);
}


}
