<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

/**
 * Página de cadastro de novo usuário.
 *
 * Após salvar, redireciona para a lista de usuários.
 * A senha é obrigatória na criação e salva com hash automaticamente
 * pelo campo dehydrateStateUsing definido no formulário do UserResource.
 */
class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /** Remove o botão "Criar e criar outro" */
    protected static bool $canCreateAnother = false;

    /** Título da página */
    public function getTitle(): string
    {
        return 'Cadastrar Usuário';
    }

    /** Redireciona para a lista após salvar */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /** Mensagem de sucesso */
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Usuário cadastrado com sucesso!';
    }
}
