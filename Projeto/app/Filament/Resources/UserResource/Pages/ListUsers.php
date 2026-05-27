<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

/**
 * Página de listagem de usuários do sistema.
 *
 * Exibe todos os almoxarifes e coordenadores cadastrados.
 * O botão "Novo Usuário" só aparece para coordenadores (canCreate).
 */
class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Novo Usuário')
                ->visible(fn () => auth()->user()?->isAlmoxarife()),
        ];
    }
}
