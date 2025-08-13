<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
implements MustVerifyEmail

{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'prenom',
        'email',
        'statut_objet',
        'password',
        'role',
        'avatar',
        'otp',
        'otp_expires_at',   
        'email_verified_at', 
    ];

      // Les statuts disponibles
    public const STATUT_ACTIF = 'actif';
    public const STATUT_INACTIF = 'inactif';
    public const STATUT_SUSPENDU = 'suspendu';
    public const STATUT_BANNI = 'banni';
    public const STATUT_SUPPRIME = 'supprimé';

    //  Optionnel : liste complète
    public const STATUTS = [
        self::STATUT_ACTIF,
        self::STATUT_INACTIF,
        self::STATUT_SUSPENDU,
        self::STATUT_BANNI,
        self::STATUT_SUPPRIME,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }


     //  Petite méthode utile pour la lisibilité
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

    public function moisComptables(){
        return $this->hasMany(MoisComptable::class, 'user_id');
    }

    public function tableaux()
    {
        return $this->hasMany(Tableau::class);
    }
    public function sousVariables()
    {
        return $this->hasMany(SousVariable::class);
    }

    public function variables()
    {
        return $this->hasMany(Variable::class);
    }
}
