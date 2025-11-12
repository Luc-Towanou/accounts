<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Categorie extends Model
{
    use HasFactory;

    /**
     * 🧭 Table associée
     */
    protected $table = 'categories';

    /**
     * 🧱 Colonnes modifiables
     */
    protected $fillable = [
        'mois_comptable_id',
        'user_id',
        'parent_id',
        'nom',
        'niveau',
        'description',
        'nature',
        'calcule',  
        'statut_objet',
        'budget_prevu',
        'depense_reelle',
        'regle_calcul',
        'is_template',
        'visibilite',
        'date_debut',
        'date_fin',
    ];

    /**
     * 🎯 Relations Eloquent
     */

    // 🔗 Catégorie parente (hiérarchie)
    public function parent()
    {
        return $this->belongsTo(Categorie::class, 'parent_id');
    }

    // 🔗 Sous-catégories (récursion)
    public function enfants()
    {
        return $this->hasMany(Categorie::class, 'parent_id')
                    ->with('enfants'); // permet une récursion infinie
    }

    // 👤 Utilisateur propriétaire
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 📆 Mois comptable associé
    public function moisComptable()
    {
        return $this->belongsTo(MoisComptable::class);
    }

    // 💰 Opérations liées à cette catégorie
    public function operations()
    {
        return $this->hasMany(Operation::class, 'category_id');
    }

    // 🧮 Règle de calcul associée
    public function regleCalcul()
    {
        return $this->hasOne(RegleCalcul::class, 'categorie_id');
    }

    /**
     * ⚙ Scopes utiles
     */

    // 🔍 Racines (catégories principales sans parent)
    public function scopeRoot(Builder $query)
    {
        return $query->whereNull('parent_id');
    }

    // 📋 Templates seulement
    public function scopeTemplates(Builder $query)
    {
        return $query->where('is_template', true);
    }

    // 👤 Catégories appartenant à un utilisateur donné
    public function scopeForUser(Builder $query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    // 🌍 Catégories publiques (visibles par tous)
    public function scopePublic(Builder $query)
    {
        return $query->where('visibilite', 'public');
    }

    /**
     * 🧠 Accessors / Mutators (optionnels)
     */

    // Retourne la somme des dépenses réelles de tous les enfants
    public function getTotalDepenseAttribute()
    {
        return $this->depense_reelle + $this->enfants->sum('total_depense');
    }

    // Retourne la somme du budget prévu avec les sous-niveaux
    public function getTotalBudgetPrevuAttribute()
    {
        return $this->budget_prevu + $this->enfants->sum('total_budget_prevu');
    }

    /**
     * 🔁 Duplication récursive d’une catégorie (pour dupliquer un template)
     */
    public function dupliquer($userId, $moisComptableId, $parentId = null)
    {
        $nouvelleCategorie = $this->replicate();
        $nouvelleCategorie->user_id = $userId;
        $nouvelleCategorie->mois_comptable_id = $moisComptableId;
        $nouvelleCategorie->parent_id = $parentId;
        $nouvelleCategorie->is_template = false;
        $nouvelleCategorie->visibilite = 'prive';
        $nouvelleCategorie->save();

        foreach ($this->enfants as $enfant) {
            $enfant->dupliquer($userId, $moisComptableId, $nouvelleCategorie->id);
        }

        return $nouvelleCategorie;
    }
}


// namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Model;

// class Categorie extends Model
// {
//     //
//         use HasFactory;
//        protected $fillable = [
//         'nom',
//         'slug',
//         'description',
//         'icon',
//         'color',
//         'statut_objet',
//     ];


//     public function variables()
//     {
//         return $this->hasMany(Variable::class);
//     }

// }
