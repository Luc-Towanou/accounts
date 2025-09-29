<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'start_date',
        'end_date',
        'variable_id',
        'sous_variable_id',
        'tableau_id',
        'type',
        'periode',
        'target_amount',
        'progress',
        'status',
    ];
    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function variable()
    {
        return $this->belongsTo(Variable::class);
    }

    public function sousVariable()
    {
        return $this->belongsTo(SousVariable::class);
    }

    public function tableau()
    {
        return $this->belongsTo(Tableau::class);
    }
    public function notifications()
    {
        return $this->hasMany(GoalNotification::class);
    }
}

