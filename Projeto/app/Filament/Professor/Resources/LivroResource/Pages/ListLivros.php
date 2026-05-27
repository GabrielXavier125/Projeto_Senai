<?php

namespace App\Filament\Professor\Resources\LivroResource\Pages;

use App\Filament\Professor\Resources\LivroResource;
use Filament\Resources\Pages\ListRecords;

/**
 * Catálogo de livros para o Coordenador — somente leitura.
 * Sem botão de criar (coordenador não cadastra livros).
 */
class ListLivros extends ListRecords
{
    protected static string $resource = LivroResource::class;

    protected function getHeaderActions(): array
    {
        return []; // sem ações de criar
    }
}
