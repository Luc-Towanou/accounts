<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegleCalcul extends Model
{
    /** @use HasFactory<\Database\Factories\RegleCalculFactory> */
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'variable_id', 
        'expression',
        'sous_variable_id',
        'statut_objet',
    ];

    public function variable()
    {
        return $this->belongsTo(Variable::class);
    }
    public function sousVariables()
    {
        return $this->belongsTo(SousVariable::class);
    }
}
