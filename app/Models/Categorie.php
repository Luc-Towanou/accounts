<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    //
        use HasFactory;
       protected $fillable = [
        'nom',
        'slug',
        'description',
        'icon',
        'color',
        'statut_objet',
    ];


    public function variables()
    {
        return $this->hasMany(Variable::class);
    }

}
