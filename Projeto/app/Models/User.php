<?php

namespace App\Models;

use App\Enums\PerfilUsuario;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'perfil',
        'materia',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'perfil'            => PerfilUsuario::class,
        ];
    }

    public function isAlmoxarife(): bool
    {
        return $this->perfil === PerfilUsuario::Almoxarife;
    }

    public function isCoordenador(): bool
    {
        return $this->perfil === PerfilUsuario::Coordenador;
    }
}
