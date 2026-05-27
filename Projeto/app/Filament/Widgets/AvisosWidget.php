<?php

namespace App\Filament\Widgets;

use App\Enums\StatusReserva;
use App\Models\Livro;
use App\Models\Reserva;
use Filament\Widgets\Widget;

/**
 * Widget de avisos críticos exibido no dashboard do almoxarife.
 *
 * Exibe dois tipos de alertas em tempo real:
 *   1. Livros com saldo abaixo do estoque mínimo (RN6)
 *   2. Reservas pendentes cujo estoque é insuficiente para atender
 *
 * Visível apenas para o almoxarife — o coordenador não precisa
 * ver os avisos operacionais do almoxarifado.
 *
 * Atualiza automaticamente a cada 30 segundos.
 */
class AvisosWidget extends Widget
{
    protected string $view = 'filament.widgets.avisos-widget';

    /** Posição no dashboard — aparece logo após o resumo de cards */
    protected static ?int $sort = 2;

    /** Ocupa a largura total */
    protected int|string|array $columnSpan = 'full';

    /** Atualiza a cada 30 segundos */
    protected ?string $pollingInterval = '30s';

    /**
     * Visível apenas para o almoxarife.
     * O coordenador não vê este widget.
     */
    public static function canView(): bool
    {
        return auth()->user()?->isAlmoxarife() ?? false;
    }

    /**
     * Passa os dados de aviso para a view.
     *
     * @return array{livrosBaixoEstoque: \Illuminate\Database\Eloquent\Collection, reservasInsuficientes: \Illuminate\Database\Eloquent\Collection}
     */
    public function getViewData(): array
    {
        // Livros com saldo <= estoque_minimo (RN6)
        $livrosBaixoEstoque = Livro::query()
            ->whereColumn('saldo_atual', '<=', 'estoque_minimo')
            ->orderBy('saldo_atual')
            ->get();

        // Reservas pendentes com estoque insuficiente para ser atendidas
        $reservasInsuficientes = Reserva::query()
            ->with(['livro', 'user'])
            ->where('status', StatusReserva::Pendente)
            ->get()
            ->filter(fn (Reserva $r) => !$r->temEstoqueSuficiente());

        return [
            'livrosBaixoEstoque'    => $livrosBaixoEstoque,
            'reservasInsuficientes' => $reservasInsuficientes,
        ];
    }
}
