<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    /** @use HasFactory<\Database\Factories\OperationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'variable_id', 
        'montant', 
        'description', 
        'date',
        'statut_objet',
        'sous_variable_id',
        'nature',];

    public function variable()
    {
        return $this->belongsTo(Variable::class);
    }

    public function sousVariable()
    {
        return $this->belongsTo(SousVariable::class);
    }

    public function cible()
    {
        return $this->variable ?? $this->sousVariable;
    }
    
    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }
    
}
