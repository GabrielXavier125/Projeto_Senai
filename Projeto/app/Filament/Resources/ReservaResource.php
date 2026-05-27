<?php

namespace App\Filament\Resources;

use App\Enums\StatusReserva;
use App\Filament\Resources\ReservaResource\Pages;
use App\Models\Livro;
use App\Models\Reserva;
use App\Services\EstoqueService;
use Filament\Actions\Action as FilamentAction;
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
 * Resource do Filament para gerenciamento de reservas de livros.
 *
 * Fluxo:
 *   1. Coordenador cria uma reserva informando livro, quantidade e turma.
 *   2. Se o estoque for insuficiente, um aviso é gerado para o almoxarife.
 *   3. O almoxarife visualiza todas as reservas pendentes.
 *   4. Quando o coordenador retira os livros, o almoxarife clica em "Dar Baixa",
 *      o que registra automaticamente uma saída no estoque.
 *
 * Visibilidade por perfil:
 *   - Coordenador: vê apenas as próprias reservas; pode criar e cancelar as próprias.
 *   - Almoxarife: vê todas as reservas; pode dar baixa e cancelar qualquer uma.
 */
class ReservaResource extends Resource
{
    protected static ?string $model = Reserva::class;

    protected static ?string $slug = 'reservas';

    protected static ?int $navigationSort = 3;

    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-bookmark';
    }

    public static function getNavigationLabel(): string
    {
        return 'Reservas';
    }

    public static function getModelLabel(): string
    {
        return 'Reserva';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Reservas';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Catálogo';
    }

    // =========================================================================
    // Filtro de visibilidade por perfil
    // =========================================================================

    /**
     * Coordenador vê apenas as próprias reservas.
     * Almoxarife vê todas.
     */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['livro', 'user']);

        if (auth()->user()?->isCoordenador()) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    // =========================================================================
    // Formulário de criação de reserva (usado pelo coordenador)
    // =========================================================================

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Section::make('Dados da Reserva')
                ->description('Informe o livro, a quantidade e a turma para qual a reserva é destinada.')
                ->schema([

                    // Seleção do livro com saldo atual visível
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
                        ->columnSpan(2),

                    // Quantidade desejada
                    TextInput::make('quantidade')
                        ->label('Quantidade')
                        ->numeric()
                        ->minValue(1)
                        ->required(),

                    // Observação obrigatória — turma / motivo da reserva
                    Textarea::make('observacao')
                        ->label('Turma / Observação')
                        ->placeholder('Ex: Turma DS-01 — Desenvolvimento de Sistemas 2026')
                        ->helperText('Informe a turma ou o motivo da reserva.')
                        ->required()
                        ->rows(3)
                        ->columnSpan(2),

                ])->columns(2),
        ]);
    }

    // =========================================================================
    // Tabela de listagem
    // =========================================================================

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                // Data da reserva
                TextColumn::make('data_reserva')
                    ->label('Reservado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(fn (Reserva $r) => $r->data_reserva->diffForHumans()),

                // Livro reservado
                TextColumn::make('livro.titulo')
                    ->label('Livro')
                    ->searchable()
                    ->limit(40)
                    ->description(fn (Reserva $r) => "Saldo atual: {$r->livro->saldo_atual}"),

                // Quantidade solicitada — badge vermelho se estoque insuficiente
                TextColumn::make('quantidade')
                    ->label('Qtd.')
                    ->alignCenter()
                    ->badge()
                    ->color(fn (Reserva $r) => $r->temEstoqueSuficiente() ? 'success' : 'danger'),

                // Status da reserva
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (StatusReserva $state) => $state->cor())
                    ->formatStateUsing(fn (StatusReserva $state) => $state->label()),

                // Quem reservou (visível para o almoxarife)
                TextColumn::make('user.name')
                    ->label('Reservado por')
                    ->sortable()
                    ->toggleable(),

                // Turma / observação
                TextColumn::make('observacao')
                    ->label('Turma / Observação')
                    ->limit(40)
                    ->placeholder('—'),

                // Data de retirada (preenchida ao dar baixa)
                TextColumn::make('data_retirada')
                    ->label('Retirado em')
                    ->dateTime('d/m/Y H:i')
                    ->placeholder('—')
                    ->toggleable(),

            ])

            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pendente'  => 'Pendentes',
                        'retirada'  => 'Retiradas',
                        'cancelada' => 'Canceladas',
                    ]),

                SelectFilter::make('livro_id')
                    ->label('Livro')
                    ->relationship('livro', 'titulo')
                    ->searchable()
                    ->preload(),
            ])

            ->actions([

                // Dar Baixa — somente almoxarife, somente reservas pendentes
                \Filament\Actions\Action::make('dar_baixa')
                    ->label('Dar Baixa')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(
                        fn (Reserva $record) =>
                            auth()->user()?->isAlmoxarife() && $record->isPendente()
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Confirmar entrega dos livros')
                    ->modalDescription(
                        fn (Reserva $record) =>
                            "Confirmar a entrega de {$record->quantidade} exemplar(es) de \"{$record->livro->titulo}\" para {$record->user->name}?"
                    )
                    ->modalSubmitActionLabel('Confirmar entrega')
                    ->action(function (Reserva $record) {
                        // Verifica o estoque atual antes de dar baixa
                        if (!$record->livro->temSaldoSuficiente($record->quantidade)) {
                            Notification::make()
                                ->title('Estoque insuficiente')
                                ->body(
                                    "Saldo atual: {$record->livro->saldo_atual} | Solicitado: {$record->quantidade}. " .
                                    "Abasteça o estoque antes de dar a baixa."
                                )
                                ->danger()
                                ->send();
                            return;
                        }

                        // Registra a saída via EstoqueService (RN1, RN5)
                        $observacao = "Baixa de reserva #{$record->id} — {$record->user->name}";
                        if ($record->observacao) {
                            $observacao .= " — {$record->observacao}";
                        }

                        app(EstoqueService::class)->registrarSaida(
                            livro:      $record->livro,
                            quantidade: $record->quantidade,
                            usuario:    auth()->user(),
                            observacao: $observacao,
                        );

                        // Atualiza status da reserva para "Retirada"
                        $record->update([
                            'status'        => StatusReserva::Retirada,
                            'data_retirada' => now(),
                        ]);

                        Notification::make()
                            ->title('Baixa realizada!')
                            ->body("Saída de {$record->quantidade} exemplar(es) registrada com sucesso.")
                            ->success()
                            ->send();
                    }),

                // Cancelar — almoxarife cancela qualquer pendente;
                // coordenador cancela somente as próprias
                \Filament\Actions\Action::make('cancelar')
                    ->label('Cancelar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Reserva $record) => $record->isPendente())
                    ->requiresConfirmation()
                    ->modalHeading('Cancelar reserva')
                    ->modalDescription('Tem certeza que deseja cancelar esta reserva? Esta ação não pode ser desfeita.')
                    ->modalSubmitActionLabel('Sim, cancelar')
                    ->action(function (Reserva $record) {
                        $record->update(['status' => StatusReserva::Cancelada]);

                        Notification::make()
                            ->title('Reserva cancelada')
                            ->warning()
                            ->send();
                    }),

            ])

            ->bulkActions([])

            ->defaultSort('data_reserva', 'desc');
    }

    // =========================================================================
    // Controle de acesso
    // =========================================================================

    /** Ambos os perfis podem criar reservas */
    public static function canCreate(): bool
    {
        return auth()->check();
    }

    /** Reservas não podem ser editadas após a criação */
    public static function canEdit(Model $record): bool
    {
        return false;
    }

    /** Ninguém exclui reservas — elas são canceladas */
    public static function canDelete(Model $record): bool
    {
        return false;
    }

    /** Ambos os perfis podem visualizar (filtrado por getEloquentQuery) */
    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    // =========================================================================
    // Páginas
    // =========================================================================

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListReservas::route('/'),
            'create' => Pages\CreateReserva::route('/create'),
        ];
    }
}
