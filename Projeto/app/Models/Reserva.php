<?php

namespace App\Models;

use App\Enums\StatusReserva;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model de Reserva de Livros.
 *
 * Representa a solicitação de retirada feita por um coordenador.
 * O almoxarife visualiza as reservas pendentes e dá baixa quando
 * os livros são entregues fisicamente.
 *
 * Uma reserva pode ser criada mesmo com estoque insuficiente —
 * neste caso, um aviso é gerado para o almoxarife.
 *
 * @property int            $id
 * @property int            $livro_id
 * @property int            $user_id
 * @property int            $quantidade
 * @property StatusReserva  $status
 * @property string|null    $observacao
 * @property \Carbon\Carbon $data_reserva
 * @property \Carbon\Carbon|null $data_retirada
 */
class Reserva extends Model
{
    /** Nome explícito da tabela */
    protected $table = 'reservas';

    protected $fillable = [
        'livro_id',
        'user_id',
        'quantidade',
        'status',
        'observacao',
        'data_reserva',
        'data_retirada',
    ];

    protected function casts(): array
    {
        return [
            'status'        => StatusReserva::class,
            'quantidade'    => 'integer',
            'data_reserva'  => 'datetime',
            'data_retirada' => 'datetime',
        ];
    }

    // =========================================================================
    // Relacionamentos
    // =========================================================================

    /**
     * O livro que foi reservado.
     */
    public function livro(): BelongsTo
    {
        return $this->belongsTo(Livro::class);
    }

    /**
     * O coordenador que fez a reserva.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Verifica se o livro tem estoque suficiente para esta reserva.
     * Usado para exibir alertas no painel do almoxarife.
     */
    public function temEstoqueSuficiente(): bool
    {
        return $this->livro->temSaldoSuficiente($this->quantidade);
    }

    /**
     * Verifica se a reserva ainda pode receber baixa.
     */
    public function isPendente(): bool
    {
        return $this->status === StatusReserva::Pendente;
    }
}
