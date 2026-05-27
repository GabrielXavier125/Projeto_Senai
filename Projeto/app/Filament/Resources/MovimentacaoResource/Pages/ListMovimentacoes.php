<?php

namespace App\Filament\Resources\MovimentacaoResource\Pages;

use App\Filament\Resources\MovimentacaoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

/**
 * Página de histórico de movimentações.
 *
 * Exibe todas as entradas e saídas registradas no sistema,
 * com filtros por tipo, livro e período.
 */
class ListMovimentacoes extends ListRecords
{
    protected static string $resource = MovimentacaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Somente almoxarife registra movimentações diretas
            // Coordenador usa o sistema de Reservas
            Actions\CreateAction::make()
                ->label('Nova Movimentação')
                ->visible(fn () => auth()->user()?->isAlmoxarife()),
        ];
    }
}
