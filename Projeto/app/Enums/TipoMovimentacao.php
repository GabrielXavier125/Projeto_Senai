<?php

namespace App\Enums;

/**
 * Enum que representa os tipos de movimentação de estoque.
 *
 * ENTRADA: livros chegaram ao almoxarifado (abastecimento)
 * SAIDA:   livros foram retirados para turmas (baixa manual)
 *
 * O valor string é o que fica salvo na coluna 'tipo' do banco.
 */
enum TipoMovimentacao: string
{
    /** Chegada de livros — aumenta o saldo */
    case Entrada = 'entrada';

    /** Retirada de livros — diminui o saldo */
    case Saida = 'saida';

    /**
     * Nome legível para exibição na interface.
     */
    public function label(): string
    {
        return match($this) {
            self::Entrada => 'Entrada',
            self::Saida   => 'Saída',
        };
    }

    /**
     * Cor do badge no painel Filament.
     * Verde para entrada, vermelho para saída.
     */
    public function cor(): string
    {
        return match($this) {
            self::Entrada => 'success', // verde
            self::Saida   => 'danger',  // vermelho
        };
    }
}
