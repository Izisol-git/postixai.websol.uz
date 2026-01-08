<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ban extends Model
{
    protected $fillable = [
        'bannable_type',
        'bannable_id',
        'reason',
        'active',
        'until',
        'starts_at',
    ];
    protected $casts = [
        'active'    => 'boolean',
        'until'     => 'datetime',
        'starts_at' => 'datetime',
    ];
    public function bannable()
    {
        return $this->morphTo();
    }
}
