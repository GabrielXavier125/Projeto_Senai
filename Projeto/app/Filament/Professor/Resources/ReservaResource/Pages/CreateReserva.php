<?php

namespace App\Filament\Professor\Resources\ReservaResource\Pages;

use App\Enums\StatusReserva;
use App\Filament\Professor\Resources\ReservaResource;
use App\Models\Livro;
use App\Models\Reserva;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * Criação de reserva pelo coordenador.
 *
 * Preenche automaticamente user_id, data_reserva e status = pendente.
 * Se o estoque for insuficiente, a reserva é criada com aviso visível.
 */
class CreateReserva extends CreateRecord
{
    protected static string $resource = ReservaResource::class;

    protected static bool $canCreateAnother = false;

    public function getTitle(): string
    {
        return 'Nova Reserva';
    }

    protected function handleRecordCreation(array $data): Model
    {
        $livro = Livro::findOrFail($data['livro_id']);

        // Bloqueia a reserva se a quantidade solicitada exceder o saldo atual
        if ((int) $data['quantidade'] > $livro->saldo_atual) {
            Notification::make()
                ->title('Reserva não permitida')
                ->body(
                    "A quantidade solicitada ({$data['quantidade']}) é maior que o " .
                    "saldo disponível de \"{$livro->titulo}\" ({$livro->saldo_atual} exemplar(es)). " .
                    "Reduza a quantidade ou aguarde o abastecimento."
                )
                ->danger()
                ->persistent()
                ->send();

            $this->halt(); // cancela o salvamento sem lançar exceção
        }

        // Preenche os campos automáticos e cria a reserva
        $data['user_id']      = auth()->id();
        $data['data_reserva'] = now();
        $data['status']       = StatusReserva::Pendente;

        return Reserva::create($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Reserva criada com sucesso!';
    }
}
