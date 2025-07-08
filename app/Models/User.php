<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'created_at',
        'updated_at',
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
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,
        ];
    }

    /**
     * Accessor for role.
     *
     * @return string
     */
    public function getRoleAttribute($value)
    {
        // This can be used to return the role in a user-friendly manner.
        return ucfirst($value);
    }

    /**
     * Get the user's created at timestamp.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function createdAt()
    {
        return $this->created_at;
    }

    /**
     * Get the user's updated at timestamp.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function updatedAt()
    {
        return $this->updated_at;
    }
}
