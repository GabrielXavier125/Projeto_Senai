<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LivroResource\Pages;
use App\Models\Livro;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Resource do Filament para gerenciamento de Livros Didáticos.
 *
 * Gera automaticamente as páginas de listagem, criação e edição
 * no painel administrativo (/admin/livros).
 *
 * Nota: Filament 5 mudou a API de Forms — usa Schema em vez de Form.
 * Section agora fica em Filament\Schemas\Components\Section.
 *
 * Controle de acesso por perfil:
 *   - Almoxarife: pode criar, editar e excluir livros
 *   - Coordenador: apenas visualiza o catálogo (sem edição)
 */
class LivroResource extends Resource
{
    /** Model Eloquent que este resource gerencia */
    protected static ?string $model = Livro::class;

    /** Ordem de exibição no menu lateral */
    protected static ?int $navigationSort = 1;

    /*
     * Filament 5 mudou os tipos de várias propriedades de navegação
     * para aceitar BackedEnum|string|null. Para evitar conflito de tipos
     * com a classe pai, usamos métodos override em vez de propriedades.
     */

    /** Ícone exibido no menu lateral */
    public static function getNavigationIcon(): string|\BackedEnum|null
    {
        return 'heroicon-o-book-open';
    }

    /** Rótulo exibido no menu lateral */
    public static function getNavigationLabel(): string
    {
        return 'Livros';
    }

    /** Nome do modelo no singular (usado em títulos e botões) */
    public static function getModelLabel(): string
    {
        return 'Livro';
    }

    /** Nome do modelo no plural (usado na listagem) */
    public static function getPluralModelLabel(): string
    {
        return 'Livros';
    }

    /** Grupo do menu lateral — agrupa com futuros itens de Estoque */
    public static function getNavigationGroup(): ?string
    {
        return 'Catálogo';
    }

    // =========================================================================
    // Formulário de criação e edição (Filament 5: Schema em vez de Form)
    // =========================================================================

    /**
     * Define os campos do formulário de cadastro/edição de livros.
     *
     * Campos exibidos: Título, ISBN, Matéria e Estoque Mínimo.
     * O saldo atual NÃO é editável aqui — ele é controlado
     * exclusivamente pelas movimentações de entrada e saída.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            // Seção com os dados de identificação do livro
            Section::make('Dados do Livro')
                ->description('Informações de identificação do livro no catálogo.')
                ->schema([

                    // Título — campo principal, ocupa 2 colunas
                    TextInput::make('titulo')
                        ->label('Título')
                        ->placeholder('Ex: Matemática para Técnicos')
                        ->required()
                        ->maxLength(200)
                        ->columnSpan(2),

                    // ISBN — identificador único internacional (RN3)
                    // unique(ignoreRecord: true) permite editar sem conflitar consigo mesmo
                    TextInput::make('isbn')
                        ->label('ISBN')
                        ->placeholder('Ex: 978-3-16-148410-0')
                        ->required()
                        ->maxLength(20)
                        ->unique(table: 'livros', column: 'isbn', ignoreRecord: true),

                    // Matéria/disciplina do livro
                    TextInput::make('materia')
                        ->label('Matéria')
                        ->placeholder('Ex: Informática Industrial')
                        ->required()
                        ->maxLength(188),

                ])->columns(2),

            // Seção de configuração de estoque mínimo
            Section::make('Configuração de Estoque')
                ->description('Define o limite para alerta de baixo estoque.')
                ->schema([

                    // Nível mínimo — alerta é exibido quando saldo <= este valor (RN6)
                    TextInput::make('estoque_minimo')
                        ->label('Estoque Mínimo')
                        ->helperText('Alerta será exibido quando o saldo atingir ou cair abaixo deste valor.')
                        ->numeric()
                        ->default(10)
                        ->minValue(1)
                        ->required(),

                ]),
        ]);
    }

    // =========================================================================
    // Tabela de listagem
    // =========================================================================

    /**
     * Define as colunas e filtros da listagem de livros.
     *
     * Exibe: Título, ISBN, Matéria, Saldo Atual (com cor) e Estoque Mínimo.
     * Permite busca por título, ISBN e matéria.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                // Título — coluna principal, pesquisável e em negrito
                TextColumn::make('titulo')
                    ->label('Título')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                // ISBN — pesquisável, monoespaçado e copiável com um clique
                TextColumn::make('isbn')
                    ->label('ISBN')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('ISBN copiado!')
                    ->fontFamily('mono'),

                // Matéria — pesquisável e filtrável
                TextColumn::make('materia')
                    ->label('Matéria')
                    ->searchable()
                    ->sortable(),

                // Saldo atual — exibido como badge colorido
                // Verde: ok | Amarelo: abaixo do mínimo | Vermelho: zerado
                TextColumn::make('saldo_atual')
                    ->label('Saldo')
                    ->badge()
                    ->color(function (Livro $record): string {
                        if ($record->saldo_atual <= 0) {
                            return 'danger';   // vermelho — sem estoque
                        }
                        if ($record->estaBaixoEstoque()) {
                            return 'warning';  // amarelo — abaixo do mínimo
                        }
                        return 'success';      // verde — estoque ok
                    })
                    ->sortable(),

                // Estoque mínimo configurado
                TextColumn::make('estoque_minimo')
                    ->label('Mínimo')
                    ->sortable()
                    ->alignCenter(),

                // Data de cadastro — oculta por padrão, ativável pelo usuário
                TextColumn::make('created_at')
                    ->label('Cadastrado em')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])

            // Filtros rápidos disponíveis na listagem
            ->filters([

                // Exibe apenas livros com saldo <= estoque_minimo
                Filter::make('baixo_estoque')
                    ->label('Somente baixo estoque')
                    ->query(fn ($query) => $query->whereColumn('saldo_atual', '<=', 'estoque_minimo')),

                // Exibe apenas livros sem nenhuma unidade em estoque
                Filter::make('sem_estoque')
                    ->label('Sem estoque (saldo = 0)')
                    ->query(fn ($query) => $query->where('saldo_atual', 0)),

            ])

            // Ações disponíveis em cada linha da tabela
            // visible() garante que o Coordenador não veja os botões (Filament 5)
            ->actions([
                EditAction::make()
                    ->visible(fn () => auth()->user()?->isAlmoxarife()),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->isAlmoxarife()),
            ])

            // Ações de seleção múltipla — somente almoxarife
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ])->visible(fn () => auth()->user()?->isAlmoxarife()),
            ])

            // Ordenação padrão: título crescente (A → Z)
            ->defaultSort('titulo', 'asc');
    }

    // =========================================================================
    // Controle de acesso por perfil (RF2)
    // =========================================================================

    /**
     * Apenas almoxarife pode criar novos livros no catálogo.
     */
    public static function canCreate(): bool
    {
        return auth()->user()?->isAlmoxarife() ?? false;
    }

    /**
     * Apenas almoxarife pode editar registros existentes.
     */
    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->isAlmoxarife() ?? false;
    }

    /**
     * Apenas almoxarife pode excluir livros do catálogo.
     */
    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->isAlmoxarife() ?? false;
    }

    /**
     * Ambos os perfis podem visualizar a lista de livros.
     */
    public static function canViewAny(): bool
    {
        return auth()->check();
    }

    // =========================================================================
    // Páginas do Resource
    // =========================================================================

    /**
     * Mapeia as rotas para as páginas deste resource.
     */
    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLivros::route('/'),
            'create' => Pages\CreateLivro::route('/create'),
            'edit'   => Pages\EditLivro::route('/{record}/edit'),
        ];
    }
}
