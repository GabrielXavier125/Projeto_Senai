<?php

namespace App\Filament\Resources\LivroResource\Pages;

use App\Filament\Resources\LivroResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

/**
 * Página de listagem de livros do painel administrativo.
 *
 * Exibe a tabela com todos os livros cadastrados no catálogo.
 * O botão "Novo Livro" só aparece para usuários com perfil Almoxarife
 * (controlado por LivroResource::canCreate()).
 */
class ListLivros extends ListRecords
{
    /** Vincula esta página ao LivroResource */
    protected static string $resource = LivroResource::class;

    /**
     * Ações exibidas no cabeçalho da listagem.
     * O CreateAction exibe o botão "Novo Livro".
     */
    protected function getHeaderActions(): array
    {
        return [
            // visible() explícito necessário no Filament 5 — canCreate() protege a rota
            // mas não esconde o botão automaticamente na listagem
            Actions\CreateAction::make()
                ->label('Novo Livro')
                ->visible(fn () => auth()->user()?->isAlmoxarife()),
        ];
    }
}
