<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name'
    ];
    public function users()
    {
        return $this->hasMany(User::class);
    }
    // in User.php, UserPhone.php, Department.php
    public function ban()
    {
        return $this->morphOne(\App\Models\Ban::class, 'bannable');
    }
    public function limit()
    {
        return $this->morphOne(Limit::class, 'limitable');
    }
}
