<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
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

}
