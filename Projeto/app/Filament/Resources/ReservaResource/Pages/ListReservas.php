<?php

namespace App\Filament\Resources\ReservaResource\Pages;

use App\Filament\Resources\ReservaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

/**
 * Página de listagem de reservas.
 *
 * Coordenador: vê apenas as próprias reservas.
 * Almoxarife: vê todas as reservas de todos os coordenadores.
 *
 * O botão "Nova Reserva" está disponível para ambos os perfis.
 */
class ListReservas extends ListRecords
{
    protected static string $resource = ReservaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Nova Reserva'),
        ];
    }
}
