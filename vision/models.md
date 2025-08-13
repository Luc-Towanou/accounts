// app/Models/MoisComptable.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; use Illuminate\Database\Eloquent\Model;

class MoisComptable extends Model { use HasFactory;

protected $fillable = ['user_id', 'mois', 'annee'];

public function user()
{
    return $this->belongsTo(User::class);
}

public function tableaux()
{
    return $this->hasMany(Tableau::class);
}

}

// app/Models/Tableau.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; use Illuminate\Database\Eloquent\Model;

class Tableau extends Model { use HasFactory;

protected $fillable = ['mois_comptable_id', 'nom', 'budget_prevu'];

public function moisComptable()
{
    return $this->belongsTo(MoisComptable::class);
}

public function sousTableaux()
{
    return $this->hasMany(SousTableau::class);
}

public function variables()
{
    return $this->hasMany(Variable::class);
}

}

// app/Models/SousTableau.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; use Illuminate\Database\Eloquent\Model;

class SousTableau extends Model { use HasFactory;

protected $fillable = ['tableau_id', 'nom', 'budget_prevu'];

public function tableau()
{
    return $this->belongsTo(Tableau::class);
}

public function variables()
{
    return $this->hasMany(Variable::class);
}

}

// app/Models/Variable.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; use Illuminate\Database\Eloquent\Model;

class Variable extends Model { use HasFactory;

protected $fillable = [
    'tableau_id',
    'sous_tableau_id',
    'nom',
    'type', // 'fixe' ou 'resultat'
    'budget_prevu'
];

public function tableau()
{
    return $this->belongsTo(Tableau::class);
}

public function sousTableau()
{
    return $this->belongsTo(SousTableau::class);
}

public function operations()
{
    return $this->hasMany(Operation::class);
}

public function regleCalcul()
{
    return $this->hasOne(RegleCalcul::class);
}

public function getDepenseReelleAttribute()
{
    return $this->operations->sum('montant');
}

}

// app/Models/Operation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; use Illuminate\Database\Eloquent\Model;

class Operation extends Model { use HasFactory;

protected $fillable = ['variable_id', 'montant', 'description', 'date'];

public function variable()
{
    return $this->belongsTo(Variable::class);
}

}

// app/Models/RegleCalcul.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory; use Illuminate\Database\Eloquent\Model;

class RegleCalcul extends Model { use HasFactory;

protected $fillable = ['variable_id', 'expression'];

public function variable()
{
    return $this->belongsTo(Variable::class);
}

}