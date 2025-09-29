<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SousVariable extends Model
{
        use HasFactory;
    //
    protected $fillable = [
        'user_id',
        'variable_id',
        'nom',
        'budget_prevu',
        'categorie_id',
        'depense_reelle',
        // 'regle_calcul',
        'statut_objet',
        'calcule',
    ];

    public function variable()
    {
        return $this->belongsTo(Variable::class);
    }

    public function operations()
    {
        return $this->hasMany(Operation::class);
    }
    public function regleCalcul()
    {
        return $this->hasOne(RegleCalcul::class);
    }

     public function user()
    {
        return $this->belongsTo(User::class);
    }
     public function tableaux()
    {
        return $this->belongsTo(Tableau::class, 'tableau_id');
    }
}
