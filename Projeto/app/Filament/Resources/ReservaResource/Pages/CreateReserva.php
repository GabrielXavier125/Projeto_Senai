<?php

namespace App\Filament\Resources\ReservaResource\Pages;

use App\Enums\StatusReserva;
use App\Filament\Resources\ReservaResource;
use App\Models\Livro;
use App\Models\Reserva;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * Página de criação de uma nova reserva de livros.
 *
 * Ao criar a reserva:
 *   - Preenche automaticamente user_id, data_reserva e status = pendente
 *   - Se o estoque for insuficiente, a reserva é salva assim mesmo
 *     e uma notificação de aviso é exibida ao coordenador
 *
 * O almoxarife verá a reserva no widget de avisos e na lista de reservas
 * com o badge de quantidade em vermelho indicando estoque insuficiente.
 */
class CreateReserva extends CreateRecord
{
    protected static string $resource = ReservaResource::class;

    protected static bool $canCreateAnother = false;

    public function getTitle(): string
    {
        return 'Nova Reserva';
    }

    /**
     * Intercepta a criação para preencher campos automáticos
     * e verificar o estoque antes de salvar.
     */
    protected function handleRecordCreation(array $data): Model
    {
        // Preenche os campos automáticos da reserva
        $data['user_id']      = auth()->id();
        $data['data_reserva'] = now();
        $data['status']       = StatusReserva::Pendente;

        $reserva = Reserva::create($data);

        // Verifica se há estoque suficiente para informar o coordenador
        $livro = Livro::find($data['livro_id']);

        if ($livro && !$livro->temSaldoSuficiente($data['quantidade'])) {
            // Avisa o coordenador que o estoque está insuficiente
            Notification::make()
                ->title('Atenção: Estoque insuficiente')
                ->body(
                    "Sua reserva foi criada, mas o saldo atual de \"{$livro->titulo}\" " .
                    "é de {$livro->saldo_atual} exemplar(es) — você solicitou {$data['quantidade']}. " .
                    "O almoxarife será notificado para providenciar o abastecimento."
                )
                ->warning()
                ->persistent() // mantém a notificação até o usuário fechar
                ->send();
        }

        return $reserva;
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
