<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SousTableau extends Model
{
    /** @use HasFactory<\Database\Factories\SousTableauFactory> */
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'tableau_id', 
        'nom', 
        'budget_prevu', 
        'statut_objet', 
        'date_debut', 
        'date_fin', 
        'description',
        'depense_reelle',

    ];

    public function tableau()
    {
        return $this->belongsTo(Tableau::class);
    }

    public function variables()
    {
        return $this->hasMany(Variable::class);
    }
}
