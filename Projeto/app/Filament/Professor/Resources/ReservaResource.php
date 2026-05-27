<?php

namespace App\Filament\Professor\Resources;

use App\Enums\StatusReserva;
use App\Filament\Professor\Resources\ReservaResource\Pages;
use App\Models\Livro;
use App\Models\Reserva;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Reservas de livros — painel do Coordenador.
 *
 * O coordenador vê apenas as próprias reservas e pode:
 *   - Criar novas reservas
 *   - Cancelar reservas pendentes
 *
 * A baixa (entrega) é feita exclusivamente pelo almoxarife
 * no painel /admin.
 */
class ReservaResource extends Resource
{
    protected static ?string $model = Reserva::class;

    protected static ?string $slug = 'reservas';

    protected static ?int $navigationSort = 2;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-bookmark';
    }

    public static function getNavigationLabel(): string
    {
        return 'Minhas Reservas';
    }

    public static function getModelLabel(): string
    {
        return 'Reserva';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Reservas';
    }

    /** Filtra automaticamente para mostrar só as reservas do coordenador logado */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['livro', 'user'])
            ->where('user_id', auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Section::make('Nova Reserva')
                ->description('Selecione o livro e informe a turma para qual a reserva é destinada.')
                ->schema([

                    // Seleção do livro — reativo para atualizar o helper de quantidade
                    Select::make('livro_id')
                        ->label('Livro')
                        ->options(
                            Livro::orderBy('titulo')
                                ->get()
                                ->mapWithKeys(fn (Livro $l) => [
                                    $l->id => "{$l->titulo} (Saldo: {$l->saldo_atual})",
                                ])
                        )
                        ->searchable()
                        ->required()
                        ->live() // reativo: atualiza o campo quantidade em tempo real
                        ->columnSpan(2),

                    // Quantidade — limitada ao saldo disponível do livro selecionado
                    TextInput::make('quantidade')
                        ->label('Quantidade')
                        ->numeric()
                        ->minValue(1)
                        ->required()
                        // Define o máximo dinamicamente com base no saldo do livro selecionado
                        ->maxValue(function ($get) {
                            $livroId = $get('livro_id');
                            if (!$livroId) return null;
                            $livro = Livro::find($livroId);
                            return $livro?->saldo_atual ?? null;
                        })
                        // Mostra o saldo disponível como helper para orientar o coordenador
                        ->helperText(function ($get) {
                            $livroId = $get('livro_id');
                            if (!$livroId) return 'Selecione um livro primeiro.';
                            $livro = Livro::find($livroId);
                            if (!$livro) return null;
                            if ($livro->saldo_atual <= 0) {
                                return "⛔ Este livro não possui exemplares disponíveis no momento.";
                            }
                            return "Disponível em estoque: {$livro->saldo_atual} exemplar(es).";
                        })
                        // Desabilita o campo se não há estoque disponível
                        ->disabled(function ($get) {
                            $livroId = $get('livro_id');
                            if (!$livroId) return false;
                            $livro = Livro::find($livroId);
                            return $livro && $livro->saldo_atual <= 0;
                        }),

                    Textarea::make('observacao')
                        ->label('Turma / Motivo')
                        ->placeholder('Ex: Turma DS-01 — Desenvolvimento de Sistemas 2026')
                        ->required()
                        ->rows(3)
                        ->columnSpan(2),

                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('data_reserva')
                    ->label('Reservado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(fn (Reserva $r) => $r->data_reserva->diffForHumans()),

                TextColumn::make('livro.titulo')
                    ->label('Livro')
                    ->limit(40),

                TextColumn::make('quantidade')
                    ->label('Qtd.')
                    ->alignCenter()
                    ->badge()
                    ->color(fn (Reserva $r) => $r->temEstoqueSuficiente() ? 'success' : 'danger'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (StatusReserva $state) => $state->cor())
                    ->formatStateUsing(fn (StatusReserva $state) => $state->label()),

                TextColumn::make('observacao')
                    ->label('Turma / Motivo')
                    ->limit(40)
                    ->placeholder('—'),

                TextColumn::make('data_retirada')
                    ->label('Retirado em')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('Aguardando retirada'),

            ])

            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pendente'  => 'Pendentes',
                        'retirada'  => 'Retiradas',
                        'cancelada' => 'Canceladas',
                    ]),
            ])

            ->actions([
                // Coordenador só pode cancelar as próprias reservas pendentes
                \Filament\Actions\Action::make('cancelar')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Reserva $record) => $record->isPendente())
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar reserva')
                    ->modalDescription('Tem certeza que deseja cancelar esta reserva?')
                    ->modalSubmitActionLabel('Sim, cancelar')
                    ->action(function (Reserva $record) {
                        $record->update(['status' => StatusReserva::Cancelada]);

                        Notification::make()
                            ->title('Reserva cancelada.')
                            ->warning()
                            ->send();
                    }),
            ])

            ->bulkActions([])
            ->defaultSort('data_reserva', 'desc');
    }

    public static function canCreate(): bool { return true; }
    public static function canEdit(Model $record): bool { return false; }
    public static function canDelete(Model $record): bool { return false; }
    public static function canViewAny(): bool { return true; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListReservas::route('/'),
            'create' => Pages\CreateReserva::route('/create'),
        ];
    }
}
