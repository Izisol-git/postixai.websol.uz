<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Limit;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use SoftDeletes;
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'telegram_id',
        'department_id',
        'oferta_read',
        'role_id',
        'email',
        'password',
        'state',
        'value',
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
    public function phones()
    {
        return $this->hasMany(UserPhone::class);
    }
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function ban()
    {
        return $this->morphOne(Ban::class, 'bannable');
    }
    public function catalogs()
    {
        return $this->hasMany(Catalog::class);
    }
    public function avatar()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
    public function getAvatarUrlAttribute()
    {
        try {
            $av = $this->avatar;

            // agar morphMany bo'lsa, birinchi elementni oling
            if ($av instanceof \Illuminate\Support\Collection) {
                $av = $av->first();
            }

            if ($av && isset($av->path) && $av->path) {
                return asset('storage/' . ltrim($av->path, '/'));
            }
        } catch (\Throwable $e) {
            // ignore quietly
        }

        return null;
    }

    // First letter for initials fallback
    public function getAvatarLetterAttribute()
    {
        $name = $this->name ?? $this->username ?? 'U';
        return mb_strtoupper(mb_substr($name, 0, 1));
    }
    public function limit()
    {
        return $this->morphOne(Limit::class, 'limitable');
    }
}
