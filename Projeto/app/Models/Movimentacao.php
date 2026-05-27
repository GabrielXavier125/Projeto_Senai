<?php

namespace App\Models;

use App\Enums\TipoMovimentacao;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model de Movimentação de Estoque.
 *
 * Representa cada entrada ou saída de livros registrada no sistema.
 * Movimentações são imutáveis — não devem ser editadas após criação,
 * pois formam o histórico de rastreabilidade (RN4).
 *
 * @property int               $id
 * @property int               $livro_id
 * @property int               $user_id
 * @property TipoMovimentacao  $tipo
 * @property int               $quantidade
 * @property string|null       $observacao
 * @property \Carbon\Carbon    $data_hora
 */
class Movimentacao extends Model
{
    /** Nome explícito da tabela — evita pluralização incorreta (movimentacaos → movimentacoes) */
    protected $table = 'movimentacoes';

    /**
     * Campos que podem ser preenchidos em massa.
     * Nota: user_id e data_hora são sempre definidos pelo EstoqueService,
     * nunca diretamente pelo usuário.
     */
    protected $fillable = [
        'livro_id',
        'user_id',
        'tipo',
        'quantidade',
        'observacao',
        'data_hora',
    ];

    /**
     * Conversão automática de tipos.
     * O cast do enum garante que $mov->tipo retorne TipoMovimentacao, não string.
     */
    protected function casts(): array
    {
        return [
            'tipo'       => TipoMovimentacao::class,
            'quantidade' => 'integer',
            'data_hora'  => 'datetime',
        ];
    }

    // =========================================================================
    // Relacionamentos
    // =========================================================================

    /**
     * O livro que foi movimentado.
     * Relacionamento N:1 — muitas movimentações pertencem a um livro.
     */
    public function livro(): BelongsTo
    {
        return $this->belongsTo(Livro::class);
    }

    /**
     * O usuário que registrou a movimentação (RN4).
     * Relacionamento N:1 — muitas movimentações pertencem a um usuário.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
