<?php

namespace App\Filament\Resources\MovimentacaoResource\Pages;

use App\Enums\TipoMovimentacao;
use App\Filament\Resources\MovimentacaoResource;
use App\Models\Livro;
use App\Services\EstoqueService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

/**
 * Página de registro de nova movimentação de estoque.
 *
 * Intercepta a criação padrão do Filament para chamar o EstoqueService,
 * que aplica todas as regras de negócio (RN1–RN5) em transação.
 *
 * Se o EstoqueService lançar uma exceção (ex: estoque insuficiente),
 * exibimos uma notificação de erro e não salvamos nada.
 */
class CreateMovimentacao extends CreateRecord
{
    protected static string $resource = MovimentacaoResource::class;

    /** Remove o botão "Criar e criar outro" */
    protected static bool $canCreateAnother = false;

    /** Título da página */
    public function getTitle(): string
    {
        return 'Registrar Movimentação';
    }

    /**
     * Intercepta a criação do registro para usar o EstoqueService.
     *
     * Em vez de salvar diretamente no banco, chama o serviço que:
     *   1. Valida quantidade > 0 (RN2)
     *   2. Para saídas: valida saldo disponível (RN1)
     *   3. Executa em transação: atualiza saldo + cria movimentação (RN5)
     *   4. Registra usuário e timestamp automaticamente (RN4)
     */
    protected function handleRecordCreation(array $data): Model
    {
        $livro    = Livro::findOrFail($data['livro_id']);
        $tipo     = TipoMovimentacao::from($data['tipo']);
        $service  = app(EstoqueService::class);
        $usuario  = auth()->user();

        try {
            // Chama o método correto do serviço conforme o tipo
            return match($tipo) {
                TipoMovimentacao::Entrada => $service->registrarEntrada(
                    livro:       $livro,
                    quantidade:  (int) $data['quantidade'],
                    usuario:     $usuario,
                    observacao:  $data['observacao'] ?? '',
                ),
                TipoMovimentacao::Saida => $service->registrarSaida(
                    livro:       $livro,
                    quantidade:  (int) $data['quantidade'],
                    usuario:     $usuario,
                    observacao:  $data['observacao'] ?? '',
                ),
            };

        } catch (\DomainException $e) {
            // RN1: estoque insuficiente — exibe notificação de erro e cancela
            Notification::make()
                ->title('Estoque insuficiente')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt(); // cancela o fluxo de criação sem salvar
        } catch (\InvalidArgumentException $e) {
            // RN2: quantidade inválida
            Notification::make()
                ->title('Quantidade inválida')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }
    }

    /**
     * Após criar com sucesso, volta para o histórico de movimentações.
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /** Mensagem de sucesso ao registrar */
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Movimentação registrada com sucesso!';
    }
}
