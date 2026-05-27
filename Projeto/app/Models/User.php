<?php

namespace App\Models;

use App\Enums\PerfilUsuario;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model de Usuário do SenaiStock.
 *
 * Representa os funcionários que têm acesso ao sistema.
 * Implementa FilamentUser para controlar quem pode acessar o painel /admin.
 *
 * Perfis disponíveis:
 *   - Almoxarife: registra entradas e saídas de livros
 *   - Coordenador: consulta relatórios e monitora o estoque
 *
 * @property int             $id
 * @property string          $name
 * @property string          $email
 * @property PerfilUsuario   $perfil
 * @property \Carbon\Carbon  $created_at
 * @property \Carbon\Carbon  $updated_at
 */
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Campos que podem ser preenchidos em massa (Mass Assignment).
     * Protege contra o envio de campos não autorizados pelo usuário.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'perfil',
    ];

    /**
     * Campos ocultados na serialização (ex: ao retornar JSON).
     * Nunca exponha senha ou tokens em respostas da API.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Define os tipos (casts) dos campos do model.
     * O cast do enum garante que $user->perfil retorne um PerfilUsuario, não uma string.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'perfil'            => PerfilUsuario::class, // cast automático para o Enum
        ];
    }

    // =========================================================================
    // Controle de acesso ao painel Filament (interface FilamentUser)
    // =========================================================================

    /**
     * Controla quem pode acessar cada painel Filament.
     *
     * Painel 'admin'     (/admin)     → somente Almoxarife
     * Painel 'professor' (/professor) → somente Coordenador
     *
     * Qualquer tentativa de acessar um painel sem o perfil correto
     * é bloqueada pelo Filament com 403 automaticamente.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return match($panel->getId()) {
            'admin'     => $this->isAlmoxarife(),
            'professor' => $this->isCoordenador(),
            default     => false,
        };
    }

    // =========================================================================
    // Helpers de perfil — facilitam verificações no código
    // =========================================================================

    /**
     * Verifica se o usuário é um Almoxarife.
     * Uso: if ($user->isAlmoxarife()) { ... }
     */
    public function isAlmoxarife(): bool
    {
        return $this->perfil === PerfilUsuario::Almoxarife;
    }

    /**
     * Verifica se o usuário é um Coordenador.
     * Uso: if ($user->isCoordenador()) { ... }
     */
    public function isCoordenador(): bool
    {
        return $this->perfil === PerfilUsuario::Coordenador;
    }
}
