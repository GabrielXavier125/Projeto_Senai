<?php

namespace App\Filament\Widgets;

use App\Enums\TipoMovimentacao;
use App\Models\Movimentacao;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * Widget das últimas movimentações registradas no sistema.
 *
 * Exibe as 8 movimentações mais recentes no dashboard,
 * permitindo que o almoxarife e o coordenador acompanhem
 * rapidamente o que foi registrado sem acessar o histórico completo.
 *
 * Para o histórico completo, o usuário deve acessar o menu Movimentações.
 */
class MovimentacoesRecentesWidget extends TableWidget
{
    /** Título do widget */
    protected static ?string $heading = 'Movimentações Recentes';

    /** Ordem de exibição — abaixo do alerta de baixo estoque */
    protected static ?int $sort = 3;

    /** Ocupa a largura total */
    protected int|string|array $columnSpan = 'full';

    /** Atualiza a cada 30 segundos */
    protected ?string $pollingInterval = '30s';

    /**
     * Configura a tabela com as últimas movimentações.
     * Limitado a 8 registros para não sobrecarregar o dashboard.
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                // As 8 movimentações mais recentes
                Movimentacao::query()
                    ->with(['livro', 'user']) // eager load para evitar N+1
                    ->latest('data_hora')
                    ->limit(8)
            )
            ->columns([

                // Data e hora da movimentação
                TextColumn::make('data_hora')
                    ->label('Quando')
                    ->dateTime('d/m/Y H:i')
                    ->description(fn (Movimentacao $r) => $r->data_hora->diffForHumans()),

                // Tipo como badge colorido
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (TipoMovimentacao $state) => $state->cor())
                    ->formatStateUsing(fn (TipoMovimentacao $state) => $state->label()),

                // Título do livro
                TextColumn::make('livro.titulo')
                    ->label('Livro')
                    ->limit(40),

                // Quantidade movimentada
                TextColumn::make('quantidade')
                    ->label('Qtd.')
                    ->alignCenter(),

                // Quem registrou
                TextColumn::make('user.name')
                    ->label('Registrado por'),

            ])
            ->emptyStateHeading('Nenhuma movimentação ainda')
            ->emptyStateDescription('As movimentações registradas aparecerão aqui.')
            ->emptyStateIcon('heroicon-o-arrows-right-left')
            ->paginated(false); // sem paginação — só as 8 mais recentes
    }
}
