<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recurrence extends Model
{
    //
    protected $fillable = [
        'tableau_id', 'variable_id', 'sous_variable_id', 'operation_id',
        'frequence', 'interval', 'date_debut', 'date_fin'
    ];

    public function tableau() { 
        return $this->belongsTo(Tableau::class); 
    }
    public function variable() { 
        return $this->belongsTo(Variable::class); 
    }
    public function sousVariable() {
         return $this->belongsTo(SousVariable::class); 
    }
    public function operation() { 
        return $this->belongsTo(Operation::class); 
    }

}
