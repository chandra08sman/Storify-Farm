<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLES = ['admin', 'supervisor', 'petugas'];

    protected $fillable = [
        'name',
        'owner_id',
        'email',
        'password',
        'role',
        'theme',
        'accent',
        'avatar_path',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'theme' => $this->theme,
            'accent' => $this->accent,
            'avatar' => $this->avatar_path ? asset('storage/'.$this->avatar_path) : null,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function workspaceOwnerId(): int
    {
        return (int) ($this->owner_id ?: $this->id);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
