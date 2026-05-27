<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model do Livro Didático.
 *
 * Representa um título do catálogo de livros do SENAI.
 * Cada livro tem um saldo em tempo real que é atualizado
 * a cada entrada ou saída registrada no sistema.
 *
 * @property int    $id
 * @property string $titulo
 * @property string $isbn
 * @property string $materia
 * @property int    $saldo_atual
 * @property int    $estoque_minimo
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Livro extends Model
{
    use HasFactory;

    /**
     * Campos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'titulo',
        'isbn',
        'materia',
        'saldo_atual',
        'estoque_minimo',
    ];

    /**
     * Conversão automática de tipos dos campos.
     */
    protected function casts(): array
    {
        return [
            'saldo_atual'     => 'integer',
            'estoque_minimo'  => 'integer',
        ];
    }

    // =========================================================================
    // Relacionamentos
    // =========================================================================

    /**
     * Um livro pode ter muitas movimentações (entradas e saídas).
     * Relacionamento 1:N com a tabela 'movimentacoes'.
     */
    public function movimentacoes(): HasMany
    {
        return $this->hasMany(Movimentacao::class);
    }

    /**
     * Um livro pode ter muitas reservas de coordenadores.
     * Relacionamento 1:N com a tabela 'reservas'.
     */
    public function reservas(): HasMany
    {
        return $this->hasMany(Reserva::class);
    }

    // =========================================================================
    // Helpers de negócio
    // =========================================================================

    /**
     * Verifica se o livro está com estoque abaixo do mínimo configurado.
     * Usado pelo monitoramento de baixo estoque (RF8).
     */
    public function estaBaixoEstoque(): bool
    {
        return $this->saldo_atual <= $this->estoque_minimo;
    }

    /**
     * Verifica se há saldo suficiente para uma saída.
     * Usado pelo EstoqueService para aplicar a RN1.
     */
    public function temSaldoSuficiente(int $quantidade): bool
    {
        return $this->saldo_atual >= $quantidade;
    }
}
