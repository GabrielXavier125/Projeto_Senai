<?php

namespace App\Filament\Widgets;

use App\Models\Livro;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * Widget de alerta de baixo estoque exibido no dashboard.
 *
 * Lista todos os livros cujo saldo atual está abaixo ou igual ao
 * estoque mínimo configurado (RN6). Serve como painel de monitoramento
 * para o coordenador identificar rapidamente o que precisa ser reposto.
 *
 * Exibido apenas quando há livros em situação de alerta.
 */
class BaixoEstoqueWidget extends TableWidget
{
    /** Título do widget no dashboard */
    protected static ?string $heading = 'Livros com Baixo Estoque';

    /** Ordem de exibição — abaixo do resumo */
    protected static ?int $sort = 2;

    /** Ocupa a largura total do dashboard */
    protected int|string|array $columnSpan = 'full';

    /** Atualiza a cada 60 segundos */
    protected ?string $pollingInterval = '60s';

    /**
     * Configura a tabela com os livros em situação de baixo estoque.
     * Ordenados por saldo crescente — os mais críticos aparecem primeiro.
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Filtra livros com saldo <= estoque_minimo (RN6)
                Livro::query()
                    ->whereColumn('saldo_atual', '<=', 'estoque_minimo')
                    ->orderBy('saldo_atual') // mais críticos primeiro
            )
            ->columns([

                // Título do livro
                TextColumn::make('titulo')
                    ->label('Livro')
                    ->searchable()
                    ->limit(50),

                // Matéria/disciplina
                TextColumn::make('materia')
                    ->label('Matéria')
                    ->badge()
                    ->color('gray'),

                // Saldo atual — vermelho para indicar urgência
                TextColumn::make('saldo_atual')
                    ->label('Saldo Atual')
                    ->alignCenter()
                    ->badge()
                    ->color(fn (Livro $record) => $record->saldo_atual === 0 ? 'danger' : 'warning'),

                // Estoque mínimo configurado para referência
                TextColumn::make('estoque_minimo')
                    ->label('Mínimo')
                    ->alignCenter()
                    ->color('gray'),

            ])
            ->emptyStateHeading('Nenhum livro em baixo estoque')
            ->emptyStateDescription('Todos os títulos estão com saldo acima do mínimo.')
            ->emptyStateIcon('heroicon-o-check-circle')
            ->paginated(false); // exibe todos sem paginação (lista de alerta)
    }
}
