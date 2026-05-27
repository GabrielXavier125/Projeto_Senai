<?php

namespace App\Filament\Professor\Resources\ReservaResource\Pages;

use App\Filament\Professor\Resources\ReservaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

/**
 * Listagem das reservas do coordenador logado.
 */
class ListReservas extends ListRecords
{
    protected static string $resource = ReservaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Nova Reserva'),
        ];
    }
}
