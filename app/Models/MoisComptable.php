<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MoisComptable extends Model
{
    /** @use HasFactory<\Database\Factories\MoisComptableFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mois',
        'annee',
        'statut_objet',
        'budget_prevu', 
        'depense_reelle',
        'gains_reelle',
        'montant_net',
        'date_debut',
        'date_fin',
        'mois_num',
    ];

    protected $casts = [
    'date_debut' => 'date',
    'date_fin'   => 'date',
    ];


      // ✅ Les statuts disponibles
    public const STATUT_ACTIF = 'actif';
    public const STATUT_INACTIF = 'inactif';
    public const STATUT_SUSPENDU = 'suspendu';
    public const STATUT_BANNI = 'banni';
    public const STATUT_SUPPRIME = 'supprimé';

    // ✅ Optionnel : liste complète
    public const STATUTS = [
        self::STATUT_ACTIF,
        self::STATUT_INACTIF,
        self::STATUT_SUSPENDU,
        self::STATUT_BANNI,
        self::STATUT_SUPPRIME,
    ];

   

    // ✅ Petite méthode utile pour la lisibilité
    public function estActif(): bool
    {
        return $this->statut_objet === self::STATUT_ACTIF;
    }

    public function estSuspendu(): bool
    {
        return $this->statut_objet === self::STATUT_SUSPENDU;
    }

    public function estBanni(): bool
    {
        return $this->statut_objet === self::STATUT_BANNI;
    }

    public function user()
{
    return $this->belongsTo(User::class);
}

public function tableaux()
{
    return $this->hasMany(Tableau::class);
}

public function categories()
{
    return $this->hasMany(Categorie::class);
}

}
