<?php

namespace App\Enums;

/**
 * Enum que representa os estados possíveis de uma reserva de livro.
 *
 * Ciclo de vida da reserva:
 *   Pendente → coordenador fez a reserva, aguardando retirada
 *   Retirada → almoxarife deu baixa, livros foram entregues
 *   Cancelada → reserva cancelada antes da retirada
 */
enum StatusReserva: string
{
    /** Reserva criada, aguardando retirada pelo coordenador */
    case Pendente = 'pendente';

    /** Reserva concluída — almoxarife registrou a entrega */
    case Retirada = 'retirada';

    /** Reserva cancelada antes da retirada */
    case Cancelada = 'cancelada';

    /**
     * Retorna o nome legível do status para exibição na interface.
     */
    public function label(): string
    {
        return match($this) {
            self::Pendente  => 'Pendente',
            self::Retirada  => 'Retirada',
            self::Cancelada => 'Cancelada',
        };
    }

    /**
     * Retorna a cor do badge no painel Filament.
     */
    public function cor(): string
    {
        return match($this) {
            self::Pendente  => 'warning',
            self::Retirada  => 'success',
            self::Cancelada => 'danger',
        };
    }
}
