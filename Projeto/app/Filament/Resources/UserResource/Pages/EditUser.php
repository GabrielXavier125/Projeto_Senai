<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

/**
 * Página de edição de usuário existente.
 *
 * A senha só é atualizada se um novo valor for digitado no formulário.
 * Se o campo senha for deixado em branco, a senha atual é preservada
 * (comportamento controlado pelo `dehydrated` no UserResource::form).
 */
class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /** Título da página */
    public function getTitle(): string
    {
        return 'Editar Usuário';
    }

    /** Botão de exclusão no cabeçalho da página */
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /** Redireciona para a lista após salvar */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /** Mensagem de sucesso */
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Usuário atualizado com sucesso!';
    }
}
