<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoalNotification extends Model
{
    protected $fillable = ['goal_id', 'percentage'];

    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }
}
