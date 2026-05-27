<?php

namespace App\Filament\Resources\LivroResource\Pages;

use App\Filament\Resources\LivroResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Página de criação de um novo livro no catálogo.
 *
 * Comportamento personalizado:
 *   - Após salvar, redireciona para a listagem de livros (não para a edição)
 *   - O botão "Criar e criar outro" foi removido para simplificar o fluxo
 */
class CreateLivro extends CreateRecord
{
    /** Vincula esta página ao LivroResource */
    protected static string $resource = LivroResource::class;

    /**
     * Remove o botão "Criar e criar outro" do formulário.
     * Mantemos apenas "Criar" e "Cancelar" para um fluxo mais limpo.
     */
    protected static bool $canCreateAnother = false;

    /** Título exibido na página de criação */
    public function getTitle(): string
    {
        return 'Cadastrar Novo Livro';
    }

    /**
     * Define para onde redirecionar após salvar o livro.
     * Por padrão o Filament redireciona para a página de edição —
     * aqui forçamos o retorno para a listagem de livros.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /** Mensagem de notificação exibida após o cadastro */
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Livro cadastrado com sucesso!';
    }
}
