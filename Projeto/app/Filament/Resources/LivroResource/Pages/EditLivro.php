<?php

namespace App\Filament\Resources\LivroResource\Pages;

use App\Filament\Resources\LivroResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

/**
 * Página de edição de um livro existente.
 *
 * Exibe o formulário definido em LivroResource::form() preenchido
 * com os dados do registro selecionado.
 *
 * Importante: o saldo_atual NÃO aparece neste formulário.
 * O saldo só pode ser alterado por movimentações (entrada/saída),
 * garantindo rastreabilidade e conformidade com as regras de negócio.
 */
class EditLivro extends EditRecord
{
    /** Vincula esta página ao LivroResource */
    protected static string $resource = LivroResource::class;

    /**
     * Ações do cabeçalho da página de edição.
     * Exibe o botão de exclusão (apenas para almoxarife).
     */
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Excluir Livro'),
        ];
    }

    /** Mensagem de confirmação ao salvar */
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Livro atualizado com sucesso!';
    }
}
