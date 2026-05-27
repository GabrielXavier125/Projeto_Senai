<?php

namespace App\Filament\Widgets;

use App\Enums\TipoMovimentacao;
use App\Models\Livro;
use App\Models\Movimentacao;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Widget de resumo do estoque exibido no dashboard.
 *
 * Apresenta três indicadores principais em cards:
 *   - Total de títulos cadastrados no sistema
 *   - Quantidade de títulos com baixo estoque (abaixo do mínimo configurado)
 *   - Movimentações registradas hoje (entradas + saídas)
 *
 * Atualiza automaticamente a cada 30 segundos para manter os dados frescos.
 */
class ResumoEstoqueWidget extends StatsOverviewWidget
{
    /** Posição no dashboard — ocupa a largura total */
    protected int|string|array $columnSpan = 'full';

    /** Ordem de exibição no dashboard */
    protected static ?int $sort = 1;

    /** Atualiza os cards a cada 30 segundos */
    protected ?string $pollingInterval = '30s';

    /**
     * Retorna os cards de estatísticas exibidos no widget.
     * Cada Stat representa um indicador com label, valor e cor.
     */
    protected function getStats(): array
    {
        // Total de títulos de livros cadastrados no sistema
        $totalLivros = Livro::count();

        // Livros com saldo abaixo ou igual ao estoque mínimo (RN6)
        $baixoEstoque = Livro::whereColumn('saldo_atual', '<=', 'estoque_minimo')->count();

        // Entradas registradas hoje
        $entradasHoje = Movimentacao::query()
            ->where('tipo', TipoMovimentacao::Entrada)
            ->whereDate('data_hora', today())
            ->count();

        // Saídas registradas hoje
        $saidasHoje = Movimentacao::query()
            ->where('tipo', TipoMovimentacao::Saida)
            ->whereDate('data_hora', today())
            ->count();

        return [
            // Card 1: Total de títulos cadastrados
            Stat::make('Títulos Cadastrados', $totalLivros)
                ->description('Livros no sistema')
                ->icon('heroicon-o-book-open')
                ->color('primary'),

            // Card 2: Títulos com baixo estoque — vermelho se houver algum
            Stat::make('Baixo Estoque', $baixoEstoque)
                ->description($baixoEstoque > 0 ? 'Requer atenção!' : 'Estoque saudável')
                ->icon('heroicon-o-exclamation-triangle')
                ->color($baixoEstoque > 0 ? 'danger' : 'success'),

            // Card 3: Entradas de hoje
            Stat::make('Entradas Hoje', $entradasHoje)
                ->description('Abastecimentos registrados')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success'),

            // Card 4: Saídas de hoje
            Stat::make('Saídas Hoje', $saidasHoje)
                ->description('Retiradas registradas')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning'),
        ];
    }
}
