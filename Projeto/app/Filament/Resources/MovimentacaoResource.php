<?php

namespace App\Filament\Resources;

use App\Enums\TipoMovimentacao;
use App\Filament\Resources\MovimentacaoResource\Pages;
use App\Models\Livro;
use App\Models\Movimentacao;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Resource do Filament para registro e histórico de movimentações de estoque.
 *
 * Permite registrar entradas e saídas de livros pelo painel administrativo.
 * A lógica de negócio (validações, transação, atualização de saldo) é
 * executada pelo EstoqueService, chamado na página CreateMovimentacao.
 *
 * Movimentações são IMUTÁVEIS — não há página de edição.
 * O histórico completo é preservado para auditoria (RN4).
 *
 * Acesso: ambos os perfis (almoxarife e coordenador) podem registrar e visualizar.
 */
class MovimentacaoResource extends Resource
{
    /** Model Eloquent que este resource gerencia */
    protected static ?string $model = Movimentacao::class;

    /** Slug da URL no painel — corrige a pluralização automática incorreta */
    protected static ?string $slug = 'movimentacoes';

    /** Ordem no menu lateral (após Livros) */
    protected static ?int $navigationSort = 2;

    /** Ícone do menu lateral */
    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-arrows-right-left';
    }

    /** Rótulo no menu lateral */
    public static function getNavigationLabel(): string
    {
        return 'Movimentações';
    }

    /** Nome no singular */
    public static function getModelLabel(): string
    {
        return 'Movimentação';
    }

    /** Nome no plural */
    public static function getPluralModelLabel(): string
    {
        return 'Movimentações';
    }

    /** Mesmo grupo dos Livros no menu */
    public static function getNavigationGroup(): ?string
    {
        return 'Catálogo';
    }

    // =========================================================================
    // Formulário de registro de movimentação
    // =========================================================================

    /**
     * Formulário para registrar uma nova entrada ou saída.
     *
     * Campos coletados aqui são passados para o EstoqueService
     * em CreateMovimentacao::handleRecordCreation().
     * O user_id e data_hora são preenchidos automaticamente pelo serviço.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Section::make('Dados da Movimentação')
                ->description('Registre a entrada ou saída de livros do estoque.')
                ->schema([

                    // Seleção do livro — busca por título no banco
                    Select::make('livro_id')
                        ->label('Livro')
                        ->options(
                            // Carrega os livros com saldo atual para facilitar a escolha
                            Livro::orderBy('titulo')
                                ->get()
                                ->mapWithKeys(fn (Livro $l) => [
                                    $l->id => "{$l->titulo} (Saldo: {$l->saldo_atual})",
                                ])
                        )
                        ->searchable()
                        ->required()
                        ->columnSpan(2),

                    // Tipo da movimentação: entrada ou saída
                    Select::make('tipo')
                        ->label('Tipo')
                        ->options([
                            TipoMovimentacao::Entrada->value => 'Entrada (abastecimento)',
                            TipoMovimentacao::Saida->value   => 'Saída (retirada para turma)',
                        ])
                        ->required()
                        ->live(), // reativo: atualiza o campo observação em tempo real

                    // Quantidade — deve ser > 0 (RN2)
                    TextInput::make('quantidade')
                        ->label('Quantidade')
                        ->numeric()
                        ->minValue(1)
                        ->required(),

                ])->columns(2),

            Section::make('Observação')
                ->schema([

                    // Justificativa da operação (turma, NF, motivo)
                    // Obrigatória para saídas, opcional para entradas
                    Textarea::make('observacao')
                        ->label('Observação / Turma')
                        ->placeholder('Ex: Turma T01 — Informática Industrial 2026')
                        ->helperText(
                            fn ($get) => $get('tipo') === TipoMovimentacao::Saida->value
                                ? '⚠️ Obrigatório para saídas — informe a turma ou motivo.'
                                : 'Opcional para entradas — ex: número da nota fiscal.'
                        )
                        ->required(
                            // Só exige observação quando o tipo for saída (RF6)
                            fn ($get) => $get('tipo') === TipoMovimentacao::Saida->value
                        )
                        ->rows(3),

                ]),
        ]);
    }

    // =========================================================================
    // Tabela de histórico
    // =========================================================================

    /**
     * Tabela com o histórico completo de movimentações.
     * Permite filtrar por tipo, livro e período.
     * Não exibe ações de edição — movimentações são imutáveis.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                // Data e hora da movimentação
                TextColumn::make('data_hora')
                    ->label('Data / Hora')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->description(fn (Movimentacao $r) => $r->data_hora->diffForHumans()),

                // Tipo exibido como badge colorido (verde=entrada, vermelho=saída)
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (TipoMovimentacao $state) => $state->cor())
                    ->formatStateUsing(fn (TipoMovimentacao $state) => $state->label()),

                // Título do livro (relacionamento)
                TextColumn::make('livro.titulo')
                    ->label('Livro')
                    ->searchable()
                    ->sortable()
                    ->limit(40), // trunca títulos longos

                // Quantidade movimentada
                TextColumn::make('quantidade')
                    ->label('Qtd.')
                    ->alignCenter()
                    ->sortable(),

                // Observação / turma
                TextColumn::make('observacao')
                    ->label('Observação')
                    ->limit(50)
                    ->placeholder('—'), // exibe traço quando vazio

                // Quem registrou a movimentação (relacionamento)
                TextColumn::make('user.name')
                    ->label('Registrado por')
                    ->sortable()
                    ->toggleable(),

            ])

            ->filters([

                // Filtro por tipo (entrada ou saída)
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'entrada' => 'Entradas',
                        'saida'   => 'Saídas',
                    ]),

                // Filtro por livro específico
                SelectFilter::make('livro_id')
                    ->label('Livro')
                    ->relationship('livro', 'titulo')
                    ->searchable()
                    ->preload(),

                // Filtro por período — exibe movimentações entre duas datas
                Filter::make('periodo')
                    ->label('Período')
                    ->form([
                        DatePicker::make('data_inicio')
                            ->label('De')
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('data_fim')
                            ->label('Até')
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            // Filtra a partir da data de início (inclusive)
                            ->when(
                                $data['data_inicio'],
                                fn (Builder $q, $date) => $q->whereDate('data_hora', '>=', $date)
                            )
                            // Filtra até a data de fim (inclusive)
                            ->when(
                                $data['data_fim'],
                                fn (Builder $q, $date) => $q->whereDate('data_hora', '<=', $date)
                            );
                    })
                    // Exibe indicador de filtro ativo com as datas selecionadas
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['data_inicio'] ?? null) {
                            $indicators[] = 'De: ' . \Carbon\Carbon::parse($data['data_inicio'])->format('d/m/Y');
                        }

                        if ($data['data_fim'] ?? null) {
                            $indicators[] = 'Até: ' . \Carbon\Carbon::parse($data['data_fim'])->format('d/m/Y');
                        }

                        return $indicators;
                    }),

            ])

            // Sem ações de edição — movimentações não podem ser alteradas
            ->actions([])
            ->bulkActions([])

            // Mais recentes primeiro
            ->defaultSort('data_hora', 'desc');
    }

    // =========================================================================
    // Controle de acesso (RF2)
    // =========================================================================

    /**
     * Somente almoxarife registra movimentações diretas.
     * Coordenador utiliza o sistema de Reservas para solicitar retiradas.
     */
    public static function canCreate(): bool
    {
        return auth()->user()?->isAlmoxarife() ?? false;
    }

    /**
     * Movimentações não podem ser editadas (imutabilidade do histórico).
     */
    public static function canEdit(Model $record): bool
    {
        return false;
    }

    /**
     * Movimentações não podem ser excluídas.
     */
    public static function canDelete(Model $record): bool
    {
        return false;
    }

    /**
     * Somente almoxarife acessa o histórico de movimentações.
     * Coordenador acompanha o estoque via Reservas.
     */
    public static function canViewAny(): bool
    {
        return auth()->user()?->isAlmoxarife() ?? false;
    }

    // =========================================================================
    // Páginas do Resource
    // =========================================================================

    /**
     * Sem página de edição — movimentações são imutáveis.
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMovimentacoes::route('/'),
            'create' => Pages\CreateMovimentacao::route('/create'),
        ];
    }
}
