<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecurrenceLog extends Model
{
    //

    protected $fillable = [
        'recurrence_id', 'date_execution', 'appliquee',
    ];
}
