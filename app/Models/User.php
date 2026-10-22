<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */

    public const ROLES = [
        'Super Admin',
        'Admin QHSE',
        'Admin Operation',
        'Admin Technical',
        'Admin Crewing',
        'Admin Purchasing',
        'Admin Kapal',
        'Management',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'master_location_id',
    ];

    /**
     * ID kapal yang membatasi data akun ini; null berarti tidak dibatasi.
     * Admin Kapal tanpa kapal mendapat 0 supaya tidak melihat data apa pun.
     */
    public function vesselScope(): ?int
    {
        return $this->role === 'Admin Kapal'
            ? (int) $this->master_location_id
            : null;
    }

    public function location()
    {
        return $this->belongsTo(MasterLocation::class, 'master_location_id');
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
